<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Category;
use App\Repository\ArticleRepository;
use App\Repository\CategoryRepository;
use App\Form\ArticleType;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

use function Symfony\Component\String\u;

class HomeController extends AbstractController 
{

    #[Route(path: "/", name: "home")]
    public function index()
    {
        return $this->render('index.html.twig');
    }

    #[Route(path: "/articles", name: "articles_index")]
    public function list(
        Request $request,
        ArticleRepository $articleRepository,
        CategoryRepository $categoryRepository,
        PaginatorInterface $paginator
    ) 
    {
       $templateVars["categories"] = $categoryRepository->findAll();

       $query = $this->validateSearch(
            $request->query->get('q', null)
       );

       $rawCategoryId = $request->query->get('c', null);
       
       $searchQb = $articleRepository->search(
            $query,
            $templateVars["categoryId"] = ($rawCategoryId && filter_var($rawCategoryId, FILTER_VALIDATE_INT) 
                ? $rawCategoryId 
                : null
            )
       );

       $templateVars["articles"] = $paginator->paginate(
         $searchQb,
         $request->query->get('page', 1),
         6
       );

       return $this->render('list.html.twig', $templateVars);
    }

    #[Route(path: "/articles/{slug}-{id}", name: "articles_show", requirements: ["id" => "\d+", 'slug' => '[a-zA-Z0-9\-]+'])]
    public function show(
        ArticleRepository $articleRepository,
        string $slug,
        int $id
    ) 
    {
        $article = $articleRepository->findOneBy(["id" => $id, "slug" => $slug]);

         if (!$article) {
            return $this->redirectToRoute('articles_index');
        }

        return $this->render('show.html.twig', ["article" => $article]);
    }

    #[Route(path: "/articles/new", name: "articles_new")]
    public function new(
        Request $request,
        EntityManagerInterface $em,
        SluggerInterface $slugger
    )
    {
        $categories = $em->getRepository(Category::class)->findAll();

        $options = $this->mapThisCustom(
            static fn(Category $category) => $category->getId(),
            $categories,
            'name'
        ); 

        $articleForm = $this->createForm(
            ArticleType::class, 
            null, 
            ["categories" => $options]
        );

        $articleForm->handleRequest($request);

        if ($articleForm->isSubmitted()) {
            $data = $articleForm->getData();

            return $this->createEditArticle($data, $em, $slugger);
        }

        $templateVars = ["form" => $articleForm->createView()];

        return $this->render('new.html.twig', $templateVars);
    }

    #[Route(path: "/articles/edit/{id}", name: "articles_edit", requirements:["id" => "\d+"])]    
    public function edit(
        Request $request,
        EntityManagerInterface $em,
        SluggerInterface $slugger,
        int $id
    ) 
    {
        $article = $em->getRepository(Article::class)->find($id);
        
        if (!$article) {
            return $this->redirectToRoute('articles_index');
        }

        $categories = $em->getRepository(Category::class)->findAll();

        $options = $this->mapThisCustom(
            static fn(Category $category) => $category->getId(),
            $categories,
            'name'
        ); 

        $articleForm = $this->createForm(
            ArticleType::class, 
            null, 
            ["categories" => $options, "article" => $article]
        );

        $articleForm->handleRequest($request);

        if ($articleForm->isSubmitted()) {
            $data = $articleForm->getData();

            return $this->createEditArticle(
                $data,
                $em,
                $slugger,
                $article
            );
        }

        $templateVars = ["form" => $articleForm->createView(), "article" => $article];

        return $this->render('new.html.twig', $templateVars);
    }

    #[Route(path: "/articles/delete/{id}", name: "articles_delete", requirements:["id" => "\d+"])]
    public function delete(
        ArticleRepository $articleRepository,
        Request $request,
        int $id
    ) 
    {
        $articleNotFound = new JsonResponse([
            "message" => "Article not found",
            "value" => "error"
        ]);

        if ($request->isMethod('DELETE') && $request->isXmlHttpRequest()) {
            $article = $articleRepository->find($id);

            if (!$article) {
                return $articleNotFound;
            }

            $data = json_decode($request->getContent(), true);
            
            $deleted = array_key_exists('deleted', $data) 
                ? $data["deleted"]
                : null;

            if ($deleted === null) {
                return $articleNotFound;
            }

            $articleRepository->save(
                $article->setDeleted($deleted)
            );

            return new JsonResponse([
                "value" => "success",
                "message" => "Article updated"
            ]);
        }

        return $articleNotFound;
    }

    #[Route(path: "/articles/new/category", name: "create_new_article_category")]
    public function newCategory(
        Request $request,
        CategoryRepository $categoryRepository,
        SluggerInterface $slugger
    ) 
    {
        $categoryNotFound = new JsonResponse(["message" => "Category not found or the category has less than 3 characters"]);

        if ($request->isMethod('POST') && $request->isXmlHttpRequest()) {
            $data = json_decode($request->getContent(), true);
            $name = trim($data['name'] ?? null);

            if (!$name || strlen($name) < 3) {
                return $categoryNotFound;
            }

            $titleName = u(strtolower($name))->title()->toString();

            $exists = $categoryRepository->exists($titleName);

            if ($exists) {
                return new JsonResponse(["message" => "Category already exists"]);
            }

            $category = (new Category)
                ->setName($titleName)
                ->setSlug($slugger->slug($titleName)->lower()->toString())
                ->setCreatedAt((new \DateTime()))
                ->setUpdatedAt(null);

            $categoryRepository->save($category);
            
            return new JsonResponse([
                "success" => true,
                "id" => $category->getId(),
                "name" => $category->getName()
            ]);
        }

        return $categoryNotFound;
    }

    private function createEditArticle(
        array $data,
        EntityManagerInterface $em,
        SluggerInterface $slugger,
        ?Article $article = null
    ) 
    {
        $categoryRepository = $em->getRepository(Category::class);

        $articleRepository = $em->getRepository(Article::class);

        $validatedData = $this->validateArticleCreation(
            $data, 
            $categoryRepository,
            $articleRepository,
            $article
        );
        
        if (isset($validatedData["message"])) {
            $this->addFlash('danger', $validatedData["message"]);

            if (!$article) {
                return $this->redirectToRoute('articles_new');
            }

            return $this->redirectToRoute('articles_edit', ['id' => $article->getId()]);
        }

        $articleRepository->save(
            ($article ?? (new Article()))
                ->setCategory($validatedData["category"])
                ->setTitle($validatedData["title"])
                ->setContent($validatedData["content"])
                ->setCreatedAt($article ? $article->getCreatedAt() :(new \DateTime()))
                ->setUpdatedAt($article ? (new \DateTime()) : null)
                ->setSlug($slugger->slug($validatedData["title"])->lower()->toString())
        );

        $this->addFlash('success', 'The article was created !');

        if (!$article) {
            return $this->redirectToRoute('articles_new');
        }

        return $this->redirectToRoute('articles_edit', ['id' => $article->getId()]);
    }

    private function validateArticleCreation(
        array $data, 
        CategoryRepository $categoryRepository,
        ArticleRepository $articleRepository,
        ?Article $article 
    ): array
    {
        if (empty($data["title"]) || strlen(trim($data["title"])) < 3) {
            return ["type" => "error", "message" => "Title should have at least 3 characters"];
        }

        if (empty($data["category"]) || !($category = $categoryRepository->find($data["category"]))) {
            return ["type" => "error", "message" => "Category not found"];
        }

        if (empty($data["content"]) || strlen($data["content"]) < 100) {
            return ["type" => "error", "message" => "The content must have at least 100 characters"];
        }

        if (!$article && $articleRepository->exists(trim($data["title"]))) {
            return ["type" => "error", "message" => "Another article with this title exists!"];
        }

        return [
            "category" => $category, 
            "title" => trim($data["title"]),
            "content" => filter_var(trim($data["content"]), FILTER_SANITIZE_FULL_SPECIAL_CHARS),

        ];  
    }

    private function validateSearch(?string $searchItem): ?string 
    {
        if (empty($searchItem)) {
            return null;
        }

        return trim(mb_substr($searchItem, 0, 100));
    }

    private function mapThisCustom(
        callable $function, 
        array $items, 
        string $itemToBeMapped
    ) 
    {
        if (empty($items)) {
            return [];
        }

        $get = 'get' . u($itemToBeMapped)->title()->toString();

        $newItems = [];

        foreach ($items as $item) {
            $newItems[$item->$get()] = $function($item);
        }

        return $newItems;
    }
}   
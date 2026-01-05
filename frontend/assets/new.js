import 'bootstrap/dist/css/bootstrap.min.css';
import { Modal } from 'bootstrap';

const handleCategory = (categorySelect) => {
   const newCategoryName = document.getElementById('new_category_name');
   const saveNewCategory = document.getElementById('save_new_category');
   const categoryFlash = document.getElementById('category_flash');

   const modal = new Modal(document.getElementById('newCategoryModal'));  

   categorySelect.innerHTML += `<option id="add_new_cat" value="__create">+ Add New Category</option>`;

   categorySelect.addEventListener('change', (e) => {
   const currentVal = e.target.value;

   if (currentVal === '__create') {
      modal.show();

      categorySelect.value = '';
   }
   });

   saveNewCategory.addEventListener('click', (e) => {
   const name = newCategoryName.value.trim();

   if (!name || name.length < 3) {
      e.preventDefault();
      categoryFlash.classList.remove('d-none');
      categoryFlash.innerText = "The category can't have less than 3 characters"
      return;
   }

   fetch(newCategoryEndPoint, {
      method: 'POST',
      headers: {
         'Content-Type': 'application/json',
         'X-Requested-With': 'XMLHttpRequest',
      },
      body: JSON.stringify({ name })
   })
      .then(res => res.json())
      .then(data => {
         if (data.success) {
            const opt = document.createElement('option');
            opt.selected = 'selected';
            opt.value = data.id;
            opt.textContent = data.name;
            categorySelect.insertBefore(opt, categorySelect.lastElementChild);
            categoryFlash.classList.add('d-none');
            modal.hide();
         } else {
               const parsed = JSON.parse(data);
               categoryFlash.textContent = parsed.message || 'Error creating category';
               categoryFlash.classList.remove('d-none');
         }
      })
      .catch(() => {
         categoryFlash.textContent = 'Unexpected error';
         categoryFlash.classList.remove('d-none');
      });
   });
};

const addFormFlash = (type, message) => {
    const formFlashes = document.getElementById('form_flashes');

    if (formFlashes) {
      formFlashes.innerHTML = `
         <div class="alert alert-${ type } alert-dismissible fade show" role="alert">
            ${ message }
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
         </div>
      `;
    }
};

document.addEventListener('DOMContentLoaded', () => {
   const articleForm = document.getElementById('article_form');  
   const categorySelect = document.getElementById('article_category');

   if (articleForm && categorySelect) {
      handleCategory(categorySelect);

       articleForm.addEventListener('submit', (e) => {
          e.preventDefault(); 

         const title = document.getElementById('article_title');
         const content =   document.getElementById('article_content');

         if (!title || title.value.length < 3) {
            addFormFlash('danger', 'Title should have at least 3 characters');
           
            return;
         }

         if (!content || content.value.length < 100) {
            addFormFlash('danger', 'The content must have at least 100 characters');
      
            return;
         }

         if (categorySelect.length < 1) {
            addFormFlash('danger', 'Category not found');

            return;
         }  

         e.target.submit();
       });
   }
});
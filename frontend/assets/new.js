import 'bootstrap/dist/css/bootstrap.min.css';
import { Modal } from 'bootstrap';

document.addEventListener('DOMContentLoaded', () => {
   const categorySelect = document.getElementById('article_category');
   const newCategoryName = document.getElementById('new_category_name');
   const saveNewCategory = document.getElementById('save_new_category');
   const categoryFlash = document.getElementById('category_flash');

   if (categorySelect) {
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
   }
});
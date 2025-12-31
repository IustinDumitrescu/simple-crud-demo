import 'bootstrap/dist/css/bootstrap.min.css';

const urlToString = (value) => {
    const url = new URL(window.location.href);
    
    if (value !== '' && value !== null) {
        url.searchParams.set('c', value);
    } else {
        url.searchParams.delete('c');
    }

    window.location.href = url.toString();
};

const deleteThis = (targetId, checked) => {
     fetch(`/articles/delete/${targetId}`, {
         method: 'DELETE',
         headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
         },
         body: JSON.stringify({ deleted: checked })
      })
        .then(res => res.json())
        .then(data => {
            console.log(data);
        })
        .catch(() => {});
};

document.addEventListener('DOMContentLoaded', () => {
    const categoryFilter = document.getElementById('category_filter');

    const deletedItems = document.getElementsByClassName('deletedItems');

    if (categoryFilter) {
        categoryFilter.addEventListener('change', (e) => urlToString(e.target.value));
    }

    if (deletedItems.length > 0) {
        for (const deletedItem of deletedItems) {
            deletedItem.addEventListener('change', (e) => {
                const targetId = e.target.getAttribute('data-id');
            
                deleteThis(targetId, e.target.checked);
            });
        }
    }
});
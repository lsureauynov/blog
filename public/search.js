document.addEventListener('DOMContentLoaded', function() {
    const searchForm = document.getElementById('searchForm');
    const searchInput = document.getElementById('searchInput');
    const searchResults = document.querySelector('.search-results-container');

    // Fonction pour gérer la soumission du formulaire
    function handleFormSubmit(event) {
        event.preventDefault(); 

        const query = searchInput.value.trim();
        if (query.length === 0) {
            return;
        }

        window.location.href = `/search_results?query=${query}`;
    }

    searchInput.addEventListener('input', function() {
        const query = this.value.trim();
        if (query.length === 0) {
            searchResults.innerHTML = '';
            return;
        }


        const xhr = new XMLHttpRequest();
        xhr.onreadystatechange = function() {
            if (xhr.readyState === XMLHttpRequest.DONE) {
                if (xhr.status === 200) {
 
                    const data = JSON.parse(xhr.responseText);
                    renderSearchResults(data);
                } else {
                    // Gestion des erreurs en cas d'échec de la requête
                    console.error('Erreur lors de la recherche :', xhr.statusText);
                }
            }
        };
        xhr.onerror = function() {
            console.error('Erreur lors de la requête AJAX.');
        };
        // Utiliser la route correcte pour la recherche d'articles
        xhr.open('GET', `/search?query=${query}`, true);
        xhr.send();
    });

    searchForm.addEventListener('submit', handleFormSubmit);
});

function renderSearchResults(data){
    searchResults.innerHTML = '';
    data.forEach(article => {
        const articleDiv = document.createElement('div');
        articleDiv.classList.add('article-item'); 
        const articleLink = document.createElement('a');
        articleLink.href = `/articles/${article.id}`;
        articleLink.classList.add('article-link'); 
        articleDiv.appendChild(articleLink);

        // Créer un élément p pour le titre de l'article
        const articleTitle = document.createElement('p');
        articleTitle.textContent = article.title;
        articleLink.appendChild(articleTitle);

        // Ajouter l'élément div au conteneur des résultats de recherche
        searchResults.appendChild(articleDiv);
    });

}

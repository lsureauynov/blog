document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const searchResults = document.getElementById('searchResults');

    searchInput.addEventListener('input', function() {
        const query = this.value.trim();
        if (query.length === 0) {
            searchResults.innerHTML = '';
            return;
        }

        // Envoyer une requête AJAX au contrôleur Symfony
        const xhr = new XMLHttpRequest();
        xhr.onreadystatechange = function() {
            if (xhr.readyState === XMLHttpRequest.DONE) {
                if (xhr.status === 200) {
                    // Traitement des données en cas de succès
                    const data = JSON.parse(xhr.responseText);
                    // Mettre à jour les résultats
                    searchResults.innerHTML = '';
                    data.forEach(article => {
                        const articleLink = document.createElement('a');
                        articleLink.href = `/articles/${article.id}`;
                        articleLink.textContent = article.title;
                        const articleDiv = document.createElement('div');
                        articleDiv.appendChild(articleLink);
                        searchResults.appendChild(articleDiv);
                    });
                } else {
                    // Gestion des erreurs en cas d'échec de la requête
                    console.error('Erreur lors de la recherche :', xhr.statusText);
                }
            }
        };
        xhr.onerror = function() {
            console.error('Erreur lors de la requête AJAX.');
        };
        // Utilisez la route correcte pour la recherche d'articles
        xhr.open('GET', `/search?query=${query}`, true);
        xhr.send();
    });
});

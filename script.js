document.getElementById('searchForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const query = document.getElementById('query').value.trim();
    fetch(`search_images.php?query=${query}`)
        .then(response => response.json())
        .then(data => {
            const resultsDiv = document.getElementById('results');
            resultsDiv.innerHTML = '';

            if (data.length > 0) {
                const imageContainer = document.createElement('div');
                imageContainer.className = 'image-container';

                data.forEach(image => {
                    const imageDiv = document.createElement('div');
                    imageDiv.className = 'image';

                    const img = document.createElement('img');
                    img.src = image.path;
                    img.alt = image.name;

                    const title = document.createElement('p');
                    title.textContent = 'Nom: ' + image.name;

                    const description = document.createElement('p');
                    description.textContent = 'Description: ' + image.description;

                    imageDiv.appendChild(img);
                    imageDiv.appendChild(title);
                    imageDiv.appendChild(description);

                    imageContainer.appendChild(imageDiv);
                });

                resultsDiv.appendChild(imageContainer);
            } else {
                resultsDiv.innerHTML = '<p>Aucune image trouvée</p>';
            }
        })
        .catch(error => console.error('Erreur:', error));
});

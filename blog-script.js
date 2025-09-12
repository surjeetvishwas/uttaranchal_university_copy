async function fetchPosts() {
  const response = await fetch('https://blog.uudoon.in/wp-json/wp/v2/posts?per_page=3');
  const posts = await response.json();
  return posts;
}

async function fetchFeaturedMedia(mediaUrl) {
  const response = await fetch(mediaUrl);
  const media = await response.json();
  return media.guid.rendered;
}

function truncateTitle(title, maxLength) {
  if (title.length <= maxLength) return title;
  return title.substr(0, maxLength - 3) + '...';
}

async function displayPosts() {
  const posts = await fetchPosts();
  const postsContainer = document.getElementById('posts-container');

  for (const post of posts) {
    const fullTitle = post.title.rendered;
    const title = truncateTitle(fullTitle, 71);
    const excerpt = post.excerpt.rendered;
    const link = post.link;
    const date = new Date(post.date).toLocaleDateString();

    const mediaUrl = post._links['wp:featuredmedia'][0].href;
    const imageUrl = await fetchFeaturedMedia(mediaUrl);

    const postHtml = `
      <div class="col-md-4 mb-4">
        <div class="card w-100 h-100 shadow-sm">
          <img src="${imageUrl}" class="card-img-top" alt="${fullTitle}">
          <div class="card-body">
            <a href="${link}" class="text-decoration-none text-dark link-success fw-bold" target="_blank"><h5 class="card-title" title="${fullTitle}">${title}</h5></a>
            <p class="card-text"><small class="text-muted">Published on ${date}</small></p>
            
          </div>
          <div class="p-3">
            <a href="${link}" class="btn btn-primary" target="_blank">Read More</a>
          </div>
        </div>
      </div>
    `;

    postsContainer.innerHTML += postHtml;
  }
}

// Call the function to display posts
displayPosts();
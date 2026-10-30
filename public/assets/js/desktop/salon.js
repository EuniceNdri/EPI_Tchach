document.addEventListener("DOMContentLoaded", () => {
  const searchInput = document.getElementById("publicationSearch");
  const defaultPosts = document.querySelector("main .posts:not(#searchResults)");
  const searchResults = document.getElementById("searchResults");
  if (!searchInput || !defaultPosts || !searchResults) return;

  let timer;
  const escapeHtml = (value) => String(value).replace(/[&<>"']/g, (character) => ({
    "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#039;",
  }[character]));

  const renderResults = (results) => {
    searchResults.innerHTML = "";
    if (results.length === 0) {
      searchResults.innerHTML = '<p class="search-empty">Aucun résultat trouvé.</p>';
      return;
    }

    results.forEach((result) => {
      const card = document.createElement("article");
      card.className = "post-card";
      const files = result.files.length
        ? `<div class="stats">📎 ${result.files.map((file) =>
          `<a href="${escapeHtml(file.download_url)}" class="stat">${escapeHtml(file.name)}</a>`
        ).join(", ")}</div>`
        : "";
      card.innerHTML = `
        <div class="post-header">
          <div class="avatar">${escapeHtml(result.author.slice(0, 2).toUpperCase())}</div>
          <div class="author-info">
            <div class="author-name">${escapeHtml(result.author)}</div>
            <div class="author-meta">${escapeHtml(result.category)} · ${escapeHtml(result.date)}</div>
          </div>
        </div>
        <h2 class="post-title">${escapeHtml(result.title)}</h2>
        <p class="post-excerpt">${escapeHtml(result.excerpt)}</p>
        <div class="post-footer">
          ${files}
          <div class="stats"><span class="stat">💬 ${result.comments} réponse(s)</span></div>
        </div>`;
      searchResults.appendChild(card);
    });
  };

  const performSearch = async () => {
    const query = searchInput.value.trim();
    if (!query) {
      searchResults.hidden = true;
      defaultPosts.hidden = false;
      searchResults.innerHTML = "";
      return;
    }

    try {
      const response = await fetch(`${window.location.pathname.replace(/\/salons?$/, "")}/search?q=${encodeURIComponent(query)}`, {
        headers: { Accept: "application/json" },
      });
      const data = await response.json();
      if (!response.ok || !data.success) {
        throw new Error(data.message || "La recherche a échoué.");
      }
      renderResults(data.results);
      defaultPosts.hidden = true;
      searchResults.hidden = false;
    } catch (error) {
      renderResults([]);
      defaultPosts.hidden = true;
      searchResults.hidden = false;
      searchResults.innerHTML = `<p class="search-empty">${escapeHtml(error.message)}</p>`;
    }
  };

  searchInput.addEventListener("input", () => {
    clearTimeout(timer);
    timer = setTimeout(performSearch, 300);
  });
});

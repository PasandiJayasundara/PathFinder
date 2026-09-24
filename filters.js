/**
 * PathFinder - Search & Multi-facet Filter Helper
 */

document.addEventListener('DOMContentLoaded', () => {
  // Homepage Trending Tags Click Handler
  document.querySelectorAll('.tag-pill[data-search]').forEach(pill => {
    pill.addEventListener('click', () => {
      const searchTerm = pill.getAttribute('data-search');
      const searchInput = document.querySelector('.hero-search-input');
      const searchForm = document.querySelector('.hero-search-box');

      if (searchInput && searchForm) {
        searchInput.value = searchTerm;
        searchForm.submit();
      } else {
        const baseUrl = window.BASE_URL || '';
        window.location.href = `${baseUrl}/stories.php?q=${encodeURIComponent(searchTerm)}`;
      }
    });
  });

  // Filter Form Auto-submit on Select Change
  const filterForm = document.getElementById('storiesFilterForm');
  if (filterForm) {
    filterForm.querySelectorAll('select').forEach(select => {
      select.addEventListener('change', () => {
        filterForm.submit();
      });
    });
  }
});

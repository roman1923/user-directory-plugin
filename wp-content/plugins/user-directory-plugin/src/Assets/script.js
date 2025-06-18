document.addEventListener('DOMContentLoaded', () => {
  const tableBody = document.querySelector('.syde-user-api-table tbody');
  if (!tableBody || tableBody.children.length === 0) {
    console.warn('User table is empty. Skipping initialization.');
    return;
  }

  const cache = new Map();

  document.body.addEventListener('click', (event) => {
    const row = event.target.closest('.user-row');
    if (!row) return;

    if (event.target.tagName === 'A') {
      event.preventDefault();
    }

    const userId = row.dataset.userId;
    const detailsRow = document.getElementById(`user-details-${userId}`);
    const contentContainer = detailsRow?.querySelector('.user-details-content');

    if (!detailsRow || !contentContainer) return;

    if (detailsRow.style.display === 'table-row') {
      detailsRow.style.display = 'none';
      return;
    }

    if (cache.has(userId)) {
      contentContainer.innerHTML = cache.get(userId);
      detailsRow.style.display = 'table-row';
      return;
    }

    contentContainer.textContent = 'Loading user details...';
    detailsRow.style.display = 'table-row';

    fetch(UserDirectory.ajax_url, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: new URLSearchParams({ action: 'fetch_user_details', id: userId })
    })
      .then(res => res.text())
      .then(html => {
        if (html.includes('User not found')) {
          contentContainer.textContent = 'No details found.';
        } else {
          cache.set(userId, html);
          contentContainer.innerHTML = html;
        }
      })
      .catch(err => {
        contentContainer.textContent = 'Error loading details.';
        console.error(err);
      });
  });
});

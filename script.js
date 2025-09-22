// script.js

document.addEventListener('DOMContentLoaded', function () {
  const modal = document.getElementById('modal');
  const trailerFrame = document.getElementById('trailer');
  const closeButton = document.querySelector('.close');

  document.querySelectorAll('.film').forEach(film => {
    film.addEventListener('click', function () {
      const trailerURL = this.getAttribute('data-trailer');
      trailerFrame.src = trailerURL + "?autoplay=1";
      modal.style.display = 'flex';
    });
  });

  closeButton.addEventListener('click', function () {
    trailerFrame.src = ''; // Videoyu durdurmak için src'yi temizle
    modal.style.display = 'none';
  });

  // ESC tuşu ile kapama
  window.addEventListener('keydown', function (e) {
    if (e.key === "Escape") {
      trailerFrame.src = '';
      modal.style.display = 'none';
    }
  });
});


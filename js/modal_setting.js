'use strict';

{

  const close = document.getElementById('close');
  const modal = document.getElementById('modal');
  const mask = document.getElementById('mask');

  close.addEventListener('click', () => {
    modal.classList.add('hidden');
    mask.classList.add('hidden');
    location.assign("index.php");
    //location.assign(nextUrl);
  });

  mask.addEventListener('click', () => {
    close.click();
    location.assign("index.php");
  })
}
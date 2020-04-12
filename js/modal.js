'use strict';

{

  let siteUrl = 'http://localhost/dev/datasharing/web/';
  let nextUrl;
  let formerUrl = document.referrer;
  //document.write(formerUrl);
  if (formerUrl.indexOf('user_edit.php') !== -1) {
    //nextUrl = 'http://localhost/dev/datasharing/web/user_list.php' ;
    nextUrl = siteUrl + 'user_list.php';
  } else {
    nextUrl = siteUrl + 'index.php';
  }

  const close = document.getElementById('close');
  const modal = document.getElementById('modal');
  const mask = document.getElementById('mask');

  close.addEventListener('click', () => {
    modal.classList.add('hidden');
    mask.classList.add('hidden');
    // location.assign("index.php");
    location.assign(nextUrl);
  });

  mask.addEventListener('click', () => {
    close.click();
    //location.assign("index.php");
    location.assign(nextUrl);
  });
}
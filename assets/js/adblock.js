(function() {
  // Create bait with common ad-related class names for detection
  var bait = document.createElement('div');
  bait.className = 'ad-banner adsbox ads ad-placement doubleclick banner-ad banner_ads ad-banner-top';
  bait.style.width = '1px';
  bait.style.height = '1px';
  bait.style.position = 'absolute';
  bait.style.left = '-9999px';
  bait.style.top = '-9999px';

  document.body.appendChild(bait);

  setTimeout(function() {
    var baitBlocked = (
      !bait.parentNode ||
      bait.offsetParent === null ||
      bait.offsetHeight === 0 ||
      bait.offsetWidth === 0 ||
      window.getComputedStyle(bait).getPropertyValue('display') === 'none' ||
      window.getComputedStyle(bait).getPropertyValue('visibility') === 'hidden'
    );

    document.body.removeChild(bait);

    if (baitBlocked) {
      var modal = document.getElementById('adblock-modal');
      modal.style.display = 'flex';
      document.body.style.overflow = 'hidden';

      document.getElementById('adblock-close-btn').addEventListener('click', function() {
        modal.style.display = 'none';
        document.body.style.overflow = '';
        location.reload();
      });
    }
  }, 100);
})();
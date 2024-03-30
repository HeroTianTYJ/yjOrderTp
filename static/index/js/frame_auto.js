$(function () {
  $('iframe').on('load', function () {
    iframeSize(this);
  });
  function iframeSize (element) {
    if (element && !window.opera) {
      if (element.contentDocument && element.contentDocument.body.offsetHeight) {
        $(element).height(element.contentDocument.body.offsetHeight);
      } else if (element.document && element.document.body.scrollHeight) {
        $(element).height(element.document.body.scrollHeight);
      }
    }
  }
});

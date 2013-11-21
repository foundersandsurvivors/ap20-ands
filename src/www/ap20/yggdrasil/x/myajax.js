// simple javascript wrapper functions around clain-ajax library
// demo at http://clean-ajax.sourceforge.net/index.php?tab=demos
// cleanajax

function showError(e){
  alert(e);
}

function get(url, consumer, progress_bar, cache){
  var message = Clean.createSimpleMessage(url, consumer, showError);
  if(cache != null)
     message.cache = cache;
  if(progress_bar != null){
     var progress = new EmbeddedProgressBar(document, progress_bar);
     message.progressBar = progress;
  }
  message.effect = {steps: 12, effect:"FADE"};
  Clean.doGet(message);
}

function post(url, consumer, form){
  var message = Clean.createSimpleMessage(url, consumer, showError);
  Clean.sendFormByName(message, form, false);
}

function transform(url, xslt, consumer, progress_bar){
  var message = Clean.createMessage(url, xslt,
           consumer, true, null);
  if(progress_bar){
     var progress = new EmbeddedProgressBar(document, progress_bar);
     message.progressBar = progress;
  }
  Clean.doGet(message);
}


(function () {
  var tabs = {
    console: document.getElementById('tab-console'),
    speed: document.getElementById('tab-speed'),
    queries: document.getElementById('tab-queries'),
    memory: document.getElementById('tab-memory'),
    files: document.getElementById('tab-files')
  };

  var panes = {
    console: document.getElementById('content-console'),
    speed: document.getElementById('content-speed'),
    queries: document.getElementById('content-queries'),
    memory: document.getElementById('content-memory'),
    files: document.getElementById('content-files')
  };

  for (key in tabs) {
    tabs[key].addEventListener('click', function (event) {
      event.preventDefault();

      clearTabs();
      clearPanes();

      this.classList.add('selected');
      if (this.id == 'tab-console') {
        panes.console.classList.add('active');
      } else if (this.id == 'tab-speed') {
        panes.speed.classList.add('active');
      } else if (this.id == 'tab-queries') {
        panes.queries.classList.add('active');
      } else if (this.id == 'tab-memory') {
        panes.memory.classList.add('active');
      } else if (this.id == 'tab-files') {
        panes.files.classList.add('active');
      }
    });
  }

  function clearTabs() {
    for (key in tabs) {
      tabs[key].classList.remove('selected');
    }
  }

  function clearPanes() {
    for (key in panes) {
      panes[key].classList.remove('active');
    }
  }

  document.getElementById('tab-console').click();
})();

var PQP_DETAILS = true;
var PQP_HEIGHT = "short";

function toggleDetails() {
  var container = document.getElementById('pQp');

  if (PQP_DETAILS) {
    addClassName(container, 'hideDetails', true);
    PQP_DETAILS = false;
  } else {
    removeClassName(container, 'hideDetails');
    PQP_DETAILS = true;
  }
}

function toggleHeight() {
  var container = document.getElementById('pQp');

  if (PQP_HEIGHT == "short") {
    addClassName(container, 'tallDetails', true);
    PQP_HEIGHT = "tall";
  } else {
    removeClassName(container, 'tallDetails');
    PQP_HEIGHT = "short";
  }
}

//http://www.bigbold.com/snippets/posts/show/2630
function addClassName(objElement, strClass, blnMayAlreadyExist) {
  if (objElement.className) {
    var arrList = objElement.className.split(' ');
    if (blnMayAlreadyExist) {
      var strClassUpper = strClass.toUpperCase();
      for (var i = 0; i < arrList.length; i++) {
        if (arrList[i].toUpperCase() == strClassUpper) {
           arrList.splice(i, 1);
           i--;
        }
      }
    }
    arrList[arrList.length] = strClass;
    objElement.className = arrList.join(' ');
  } else {  
    objElement.className = strClass;
  }
}

//http://www.bigbold.com/snippets/posts/show/2630
function removeClassName(objElement, strClass) {
  if (objElement.className) {
    var arrList = objElement.className.split(' ');
    var strClassUpper = strClass.toUpperCase();
    for (var i = 0; i < arrList.length; i++) {
      if (arrList[i].toUpperCase() == strClassUpper) {
        arrList.splice(i, 1);
        i--;
      }
    }
    objElement.className = arrList.join(' ');
  }
}

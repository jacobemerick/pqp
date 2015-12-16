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
        panes.console.classList.add('active-box');
      } else if (this.id == 'tab-speed') {
        panes.speed.classList.add('active-box');
      } else if (this.id == 'tab-queries') {
        panes.queries.classList.add('active-box');
      } else if (this.id == 'tab-memory') {
        panes.memory.classList.add('active-box');
      } else if (this.id == 'tab-files') {
        panes.files.classList.add('active-box');
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
      panes[key].classList.remove('active-box');
    }
  }

  document.getElementById('tab-console').click();

  var container = document.getElementById('pQp');
  document.getElementById('toggle-details').addEventListener('click', function (event) {
    if (container.classList.contains('shrink')) {
      container.classList.remove('shrink');
      document.getElementById('toggle-height').style.display = 'block';
    } else {
      container.classList.add('shrink');
      container.classList.remove('tall');
      document.getElementById('toggle-height').style.display = 'none';
    }
  });

  document.getElementById('toggle-height').addEventListener('click', function (event) {
    if (container.classList.contains('tall')) {
      container.classList.remove('tall');
    } else {
      container.classList.add('tall');
    }
  });
})();

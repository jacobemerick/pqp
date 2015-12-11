<div id="pQp" class="console" style="display:none">
  <div class="content">
    <ul class="header">
      <li class="console" onclick="changeTab('console');">
        <h3 class="green"><?php echo $header['console'] ?></h3>
        <h4>Console</h4>
      </li>
      <li class="speed" onclick="changeTab('speed');"> 
        <h3 class="blue"><?php echo $header['speed'] ?></h3>
        <h4>Load Time</h4>
      </li>
      <li class="queries" onclick="changeTab('queries');">
        <h3 class="purple"><?php echo $header['query'] ?> Queries</h3>
        <h4>Database</h4>
      </li>
      <li class="memory" onclick="changeTab('memory');">
        <h3 class="orange"><?php echo $header['memory'] ?></h3>
        <h4>Memory Used</h4>
      </li>
      <li class="files" onclick="changeTab('files');">
        <h3 class="red"><?php echo $header['files'] ?> Files</h3>
        <h4>Included</h4>
      </li>
    </ul>

    <div id="pqp-console" class="pqp-box">
    <?php if (empty($console['messages'])) : ?>
      <p class="no-logs">This panel has no log items.</p>
    <?php else : ?>
      <ul class="meta box">
        <li class="green-background">
          <h5><?php echo $console['meta']['log'] ?></h5>
          <h6>Logs</h6>
        </li>
        <li class="red-background">
          <h5><?php echo $console['meta']['error'] ?></h5>
          <h6>Errors</h6>
        </li>
        <li class="orange-background">
          <h5><?php echo $console['meta']['memory'] ?></h5>
          <h6>Memory</h6>
        </li>
        <li class="blue-background">
          <h5><?php echo $console['meta']['speed'] ?></h5>
          <h6>Speed</h6>
        </li>
      </ul>
      <ul class="messages">
      <?php foreach ($console['messages'] as $message) : ?>
        <li class="labeled">
          <span class="type <?php echo $message['type'] ?>">
            <h5><?php echo $message['type'] ?></h5>
          </span>
          <span class="message"><?php echo $message['message'] ?></span>
          <?php if ($message['data']) : ?>
          <span class="data"><?php echo $message['data'] ?></span>
          <?php endif ?>
        </li>
      <?php endforeach ?>
      </ul>
    <?php endif ?>
    </div>

    <div id="pqp-speed" class="pqp-box">
    <?php if (empty($speed['messages'])) : ?>
      <p class="no-logs">This panel has no log items.</p>
    <?php else: ?>
      <ul class="meta">
        <li class="blue-background">
          <h5><?php echo $speed['meta']['elapsed'] ?></h5>
          <h6>Load Time</h6>
        </li>
        <li class="blue-dark-background">
          <h5><?php echo $speed['meta']['allowed'] ?></h5>
          <h6>Max Execution Time</h6>
        </li>
      </ul>
      <ul class="messages">
      <?php foreach ($speed['messages'] as $message) : ?>
        <li>
          <span class="message"><?php echo $message['message'] ?></span>
          <span class="data"><?php echo $message['data'] ?></span>
        </li>
      <?php endforeach ?>
      </ul>
    <?php endif ?>
    </div>

    <div id="pqp-queries" class="pqp-box">
    <?php if (empty($query['messages'])) : ?>
      <p class="no-logs">This panel has no log items.</p>
    <?php else : ?>
      <ul class="meta">
        <li class="purple-background">
          <h5><?php echo $query['meta']['count'] ?></h5>
          <h6>Total Queries</h6>
        </li>
        <li class="purple-dark-background">
          <h5><?php echo $query['meta']['time'] ?></h5>
          <h6>Total Time</h6>
        </li>
        <li class="purple-background">
          <h5><?php echo $query['meta']['slowest'] ?></h5>
          <h6>Slowest Query</h6>
        </li>
      </ul>
      <ul class="messages">
      <?php foreach ($query['messages'] as $message) : ?>
        <li>
          <span class="message"><?php echo $message['message'] ?></span>
          <span class="data"><?php echo $message['data'] ?></span>
          <dl class="sub-data">
          <?php foreach ($message['sub_data'] as $key => $value) : ?>
            <dt><?php echo $key ?></dt>
            <dd><?php echo $value ?></dd>
          <?php endforeach ?>
          </dl>
        </li>
      <?php endforeach ?>
      </ul>
    <?php endif ?>
    </div>

    <div id="pqp-memory" class="pqp-box">
    <?php if ($output['console']['count']['memory'] == 0): ?>
      <p class="no-logs">This panel has no log items.</p>
    <?php else: ?>
      <table class="side" cellspacing="0">
        <tr>
          <td>
            <var><?php echo $output['memory']['used'] ?></var>
            <h4>Used Memory</h4>
          </td>
        </tr>
        <tr>
          <td class="alt">
            <var><?php echo $output['memory']['allowed'] ?></var>
            <h4>Total Available</h4>
          </td>
        </tr>
      </table>
      <table class="main" cellspacing="0">';
      <?php foreach ($output['console']['messages'] as $log): ?>
        <?php if ($log['type'] == 'memory'): ?>
        <tr class="log-memory">
          <td>
            <b><?php echo $log['data'] ?></b>
            <?php if ($log['data_type']) : ?>
            <em><?php echo $log['data_type'] ?></em>:
            <?php endif ?>
            <?php echo $log['name'] ?>
          </td>
        </tr>
        <?php endif ?>
      <?php endforeach ?>
      </table>
    <?php endif ?>
    </div>

    <div id="pqp-files" class="pqp-box">
    <?php if ($output['fileTotals']['count'] == 0): ?>
      <p class="no-logs">This panel has no log items.</p>
    <?php else: ?>
      <table class="side" cellspacing="0">
        <tr>
          <td>
            <var><?php echo $output['fileTotals']['count'] ?></var>
            <h4>Total Files</h4>
          </td>
        </tr>
        <tr>
          <td class="alt">
            <var><?php echo $output['fileTotals']['size'] ?></var>
            <h4>Total Size</h4>
          </td>
        </tr>
        <tr>
          <td>
            <var><?php echo $output['fileTotals']['largest'] ?></var>
            <h4>Largest</h4>
          </td>
        </tr>
      </table>
      <table class="main" cellspacing="0">
      <?php foreach ($output['files'] as $file): ?>
        <tr>
          <td>
            <b><?php echo $file['size'] ?></b>
            <?php echo $file['name'] ?>
          </td>
        </tr>
      <?php endforeach ?>
      </table>
    <?php endif ?>
    </div>

    <table id="pqp-footer" cellspacing="0">
      <tr>
        <td class="credit">
          <a href="http://particletree.com" target="_blank">
            <strong>PHP</strong>
            <b class="green">Q</b><b class="blue">u</b><b class="purple">i</b><b class="orange">c</b><b class="red">k</b>
            Profiler
          </a>
        </td>
        <td class="actions">
          <a href="#" onclick="toggleDetails();return false">Details</a>
          <a class="heightToggle" href="#" onclick="toggleHeight();return false">Height</a>
        </td>
      </tr>
    </table>
  </div>
</div>

<script type="text/javascript">
<?php echo $script ?>
</script>

<style type="text/css">
<?php echo $styles ?>
</style>

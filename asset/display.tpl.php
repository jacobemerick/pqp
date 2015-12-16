<div id="pQp" class="pQp">
  <div class="content">
    <ul class="header">
      <li>
        <a href="javascript:void(0);" id="tab-console">
          <h3 class="green"><?php echo $header['console'] ?></h3>
          <h4>Console</h4>
        </a>
      </li>
      <li>
        <a href="javascript:void(0);" id="tab-speed">
          <h3 class="blue"><?php echo $header['speed'] ?></h3>
          <h4>Load Time</h4>
        </a>
      </li>
      <li>
        <a href="javascript:void(0);" id="tab-queries">
          <h3 class="purple"><?php echo $header['query'] ?> Queries</h3>
          <h4>Database</h4>
        </a>
      </li>
      <li>
        <a href="javascript:void(0);" id="tab-memory">
          <h3 class="orange"><?php echo $header['memory'] ?></h3>
          <h4>Memory Used</h4>
        </a>
      </li>
      <li>
        <a href="javascript:void(0);" id="tab-files">
          <h3 class="red"><?php echo $header['files'] ?> Files</h3>
          <h4>Included</h4>
        </a>
      </li>
    </ul>

    <div id="content-console" class="pqp-box">
<?php
  if (empty($console['messages'])) :
?>
      <p class="no-logs">This panel has no log items.</p>
<?php
  else :
?>
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
<?php
  foreach ($console['messages'] as $message) :
?>
        <li class="labeled">
          <span class="type <?php echo $message['type'] ?>">
            <h5><?php echo $message['type'] ?></h5>
          </span>
          <span class="message"><?php echo $message['message'] ?></span>
<?php
  if ($message['data']) :
?>
          <span class="data"><?php echo $message['data'] ?></span>
<?php
  endif
?>
        </li>
<?php
  endforeach
?>
      </ul>
<?php
  endif
?>
    </div>

    <div id="content-speed" class="pqp-box">
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

    <div id="content-queries" class="pqp-box">
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

    <div id="content-memory" class="pqp-box">
    <?php if (empty($memory['messages'])) : ?>
      <p class="no-logs">This panel has no log items.</p>
    <?php else : ?>
      <ul class="meta">
        <li class="orange-background">
          <h5><?php echo $memory['meta']['used'] ?></h5>
          <h6>Used Memory</h6>
        </li>
        <li class="orange-dark-background">
          <h5><?php echo $memory['meta']['allowed'] ?></h5>
          <h6>Total Available</h6>
        </li>
      </ul>
      <ul class="messages">
      <?php foreach ($memory['messages'] as $message) : ?>
        <li>
          <span class="message"><?php echo $message['message'] ?></span>
          <span class="data"><?php echo $message['data'] ?></span>
        </li>
      <?php endforeach ?>
      </ul>
    <?php endif ?>
    </div>

    <div id="content-files" class="pqp-box">
    <?php if (empty($files['messages'])) : ?>
      <p class="no-logs">This panel has no log items.</p>
    <?php else: ?>
      <ul class="meta">
        <li class="red-background">
          <h5><?php echo $files['meta']['count'] ?></h5>
          <h6>Total Files</h6>
        </li>
        <li class="red-dark-background">
          <h5><?php echo $files['meta']['size'] ?></h5>
          <h6>Total Size</h6>
        </li>
        <li class="red-background">
          <h5><?php echo $files['meta']['largest'] ?></h5>
          <h6>Largest</h6>
        </li>
      </ul>
      <ul class="messages">
      <?php foreach ($files['messages'] as $message) : ?>
        <li>
          <span class="message"><?php echo $message['message'] ?></span>
          <span class="data"><?php echo $message['data'] ?></span>
        </li>
      <?php endforeach ?>
      </ul>
    <?php endif ?>
    </div>

    <div class="footer">
      <h2 class="credit">
        <a href="http://particletree.com" target="_blank">
          <span class="php">PHP</span>
          <span class="green">Q</span>
          <span class="blue">u</span>
          <span class="purple">i</span>
          <span class="orange">c</span>
          <span class="red">k</span>
          <span class="profiler">Profiler</span>
        </a>
      </h2>
      <ul class="actions">
        <li><a href="javascript:void(0);" id="toggle-height">Height</a></li>
        <li><a href="javascript:void(0);" id="toggle-details">Details</a></li>
      </ul>
    </div>
  </div>
</div>

<script type="text/javascript">
<?php echo $script ?>
</script>

<style type="text/css">
<?php echo $styles ?>
</style>

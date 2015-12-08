<div id="pqp-container" class="pQp" style="display:none">
  <div id="pQp" class="console">
    <table id="pqp-metrics" cellspacing="0">
      <tr>
        <td class="green" onclick="changeTab('console');">
          <var><?php echo array_sum($output['console']['count']) ?></var>
          <h4>Console</h4>
        </td>
        <td class="blue" onclick="changeTab('speed');">
          <var><?php echo $output['speed']['elapsed'] ?></var>
          <h4>Load Time</h4>
        </td>
        <td class="purple" onclick="changeTab('queries');">
          <var><?php echo $output['queryTotals']['count'] ?> Queries</var>
          <h4>Database</h4>
        </td>
        <td class="orange" onclick="changeTab('memory');">
          <var><?php echo $output['memory']['used'] ?></var>
          <h4>Memory Used</h4>
        </td>
        <td class="red" onclick="changeTab('files');">
          <var><?php echo count($output['files']) ?> Files</var>
          <h4>Included</h4>
        </td>
      </tr>
    </table>

    <div id="pqp-console" class="pqp-box">
    <?php if (count($output['console']) == 0): ?>
      <h3>This panel has no log items.</h3>
    <?php else: ?>
      <table class="side" cellspacing="0">
        <tr>
          <td class="alt1">
            <var><?php echo $output['console']['count']['log'] ?></var>
            <h4>Logs</h4>
          </td>
          <td class="alt2">
            <var><?php echo $output['console']['count']['error'] ?></var>
            <h4>Errors</h4>
          </td>
        </tr>
        <tr>
          <td class="alt3">
            <var><?php echo $output['console']['count']['memory'] ?></var>
            <h4>Memory</h4>
          </td>
          <td class="alt4">
            <var><?php echo $output['console']['count']['speed'] ?></var>
            <h4>Speed</h4>
          </td>
        </tr>
      </table>
      <table class="main" cellspacing="0">
      <?php $class = '' ?>
      <?php foreach ($output['console']['messages'] as $log): ?>
        <tr class="log-<?php echo $log['type'] ?>">
          <td class="type"><?php echo $log['type'] ?></td>
          <td class="<?php echo $class ?>">
          <?php if ($log['type'] == 'log'): ?>
            <div><pre><?php echo $log['data'] ?></pre></div>
          <?php elseif ($log['type'] == 'memory'): ?>
            <div>
              <pre><?php echo $log['data'] ?></pre>
              <?php if (!empty($log['data_type'])): ?>
              <em><?php echo $log['data_type'] ?></em>
              <?php endif; ?>
              <?php echo $log['name'] ?>
            </div>
          <?php elseif ($log['type'] == 'speed'): ?>
            <div>
              <pre><?php echo $log['data'] ?></pre>
              <em><?php echo $log['name'] ?></em>
            </div>
          <?php elseif ($log['type'] == 'error'): ?>
            <div>
              <em>Line <?php echo $log['line'] ?></em> :
              <?php echo $log['data'] ?>
              <pre><?php echo $log['file'] ?></pre>
            </div>
          <?php endif ?>
          </td>
        </tr>
        <?php $class = ($class == '') ? 'alt' : '' ?>
      <?php endforeach ?>
      </table>
    <?php endif ?>
    </div>

    <div id="pqp-speed" class="pqp-box">
    <?php if ($output['console']['count']['speed'] == 0): ?>
      <h3>This panel has no log items.</h3>
    <?php else: ?>
      <table class="side" cellspacing="0">
        <tr>
          <td>
            <var><?php echo $output['speed']['elapsed'] ?></var>
            <h4>Load Time</h4>
          </td>
        </tr>
        <tr>
          <td class="alt">
            <var><?php echo $output['speed']['allowed'] ?></var>
            <h4>Max Execution Time</h4>
          </td>
        </tr>
      </table>
      <table class="main" cellspacing="0">
      <?php $class = '' ?>
      <?php foreach ($output['console']['messages'] as $log): ?>
        <?php if ($log['type'] == 'speed'): ?>
        <tr class="log-speed">
          <td class="<?php echo $class ?>">
            <div>
              <pre><?php echo $log['data'] ?></pre>
              <em><?php echo $log['name'] ?></em>
            </div>
          </td>
        </tr>
        <?php $class = ($class == '') ? 'alt' : '' ?>
        <?php endif ?>
      <?php endforeach ?>
      </table>
    <?php endif ?>
    </div>

    <div id="pqp-queries" class="pqp-box">
    <?php if ($output['queryTotals']['count'] == 0): ?>
      <h3>This panel has no log items.</h3>
    <?php else: ?>
      <table class="side" cellspacing="0">
        <tr>
          <td>
            <var><?php echo $output['queryTotals']['count'] ?></var>
            <h4>Total Queries</h4>
          </td>
        </tr>
        <tr>
          <td class="alt">
            <var><?php echo $output['queryTotals']['time'] ?></var>
            <h4>Total Time</h4>
          </td>
        </tr>
        <tr>
          <td>
            <var>0</var>
            <h4>Duplicates</h4>
          </td>
        </tr>
      </table>
      <table class="main" cellspacing="0">';
      <?php $class = '' ?>
      <?php foreach ($output['queries'] as $query): ?>
        <tr>
          <td class="<?php echo $class ?>">
            <?php echo $query['sql'] ?>
            <?php if ($query['explain']): ?>
            <em>
              Possible keys: <b><?php echo $query['explain']['possible_keys'] ?></b> &middot; 
              Key Used: <b><?php echo $query['explain']['key'] ?></b> &middot; 
              Type: <b><?php echo $query['explain']['type'] ?></b> &middot; 
              Rows: <b><?php echo $query['explain']['rows'] ?></b> &middot; 
              Speed: <b><?php echo $query['time'] ?></b>
            </em>
            <?php endif ?>
          </td>
        </tr>
        <?php $class = ($class == '') ? 'alt' : '' ?>
      <?php endforeach ?>
      </table>
    <?php endif ?>
    </div>

    <div id="pqp-memory" class="pqp-box">
    <?php if ($output['console']['count']['memory'] == 0): ?>
      <h3>This panel has no log items.</h3>
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
      <?php $class = '' ?>
      <?php foreach ($output['console']['messages'] as $log): ?>
        <?php if ($log['type'] == 'memory'): ?>
        <tr class="log-memory">
          <td class="<?php echo $class ?>">
            <b><?php echo $log['data'] ?></b>
            <?php if ($log['data_type']) : ?>
            <em><?php echo $log['data_type'] ?></em>:
            <?php endif ?>
            <?php echo $log['name'] ?>
          </td>
        </tr>
        <?php $class = ($class == '') ? 'alt' : '' ?>
        <?php endif ?>
      <?php endforeach ?>
      </table>
    <?php endif ?>
    </div>

    <div id="pqp-files" class="pqp-box">
    <?php if ($output['fileTotals']['count'] == 0): ?>
      <h3>This panel has no log items.</h3>
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
      <?php $class = '' ?>
      <?php foreach ($output['files'] as $file): ?>
        <tr>
          <td class="<?php echo $class ?>">
            <b><?php echo $file['size'] ?></b>
            <?php echo $file['name'] ?>
          </td>
        </tr>
        <?php $class = ($class == '') ? 'alt' : '' ?>
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

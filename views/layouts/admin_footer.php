        </div><!-- /.content-body -->
    </div><!-- /.main-content -->
</div><!-- /.dashboard-layout -->
<script src="<?= asset('js/app.js') ?>"></script>
<?php if (isset($extraJs)): foreach((array)$extraJs as $js): ?>
<script src="<?= asset('js/' . $js) ?>"></script>
<?php endforeach; endif; ?>
</body>
</html>

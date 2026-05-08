<?php $headerTitle = 'Messages'; include VIEW_PATH . '/layouts/admin_layout.php'; ?>
<div class="dashboard-card">
    <div class="table-responsive">
        <table class="data-table">
            <thead><tr><th>Name</th><th>Email</th><th>Subject</th><th>Message</th><th>Date</th><th>Actions</th></tr></thead>
            <tbody>
                <?php if (empty($messages)): ?>
                <tr><td colspan="6" style="text-align:center;padding:40px;color:var(--text-muted);">No messages yet</td></tr>
                <?php else: foreach ($messages as $m): ?>
                <tr style="<?= empty($m['is_read']) ? 'background:rgba(82,183,136,0.05);' : '' ?>">
                    <td style="font-weight:600;"><?= e($m['name']) ?></td>
                    <td><a href="mailto:<?= e($m['email']) ?>"><?= e($m['email']) ?></a></td>
                    <td><?= e($m['subject'] ?? 'No Subject') ?></td>
                    <td style="max-width:300px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= e($m['message']) ?></td>
                    <td style="color:var(--text-muted);font-size:13px;"><?= date('M d, Y', strtotime($m['created_at'])) ?></td>
                    <td>
                        <form method="POST" action="<?= base_url('admin/messages/delete/'.$m['id']) ?>" onsubmit="return confirm('Delete?')">
                            <?= csrf_field() ?><button class="btn btn-sm" style="color:var(--danger);border:1px solid var(--danger);background:none;">🗑️</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include VIEW_PATH . '/layouts/admin_footer.php'; ?>

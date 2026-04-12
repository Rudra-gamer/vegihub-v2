<?php $headerTitle = 'Users Management'; include VIEW_PATH . '/layouts/admin_layout.php'; ?>
<div style="margin-bottom:20px;display:flex;gap:8px;flex-wrap:wrap;">
    <?php foreach(['' => 'All', 'buyer' => 'Buyers', 'seller' => 'Sellers', 'admin' => 'Admins'] as $val => $label): ?>
    <a href="<?= base_url('admin/users' . ($val ? '?role='.$val : '')) ?>" class="btn btn-sm <?= ($roleFilter ?? '') === $val ? 'btn-primary' : 'btn-outline' ?>"><?= $label ?></a>
    <?php endforeach; ?>
</div>
<div class="dashboard-card">
    <div class="table-responsive">
        <table class="data-table">
            <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Joined</th><th>Actions</th></tr></thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                <tr>
                    <td style="font-weight:600;"><?= e($u['name']) ?></td>
                    <td><?= e($u['email']) ?></td>
                    <td><span class="badge <?= ['admin'=>'badge-danger','seller'=>'badge-info','buyer'=>'badge-success'][$u['role']] ?? '' ?>"><?= ucfirst($u['role']) ?></span></td>
                    <td><span class="status-dot <?= $u['status'] ?>"><?= ucfirst($u['status']) ?></span></td>
                    <td style="color:var(--text-muted);font-size:13px;"><?= date('M d, Y', strtotime($u['created_at'])) ?></td>
                    <td>
                        <div style="display:flex;gap:4px;">
                            <?php if ($u['status'] !== 'banned'): ?>
                            <form method="POST" action="<?= base_url('admin/users/ban/'.$u['id']) ?>" onsubmit="return confirm('Ban this user?')">
                                <?= csrf_field() ?><button class="btn btn-sm" style="color:var(--danger);border:1px solid var(--danger);background:none;" title="Ban">🚫</button>
                            </form>
                            <?php else: ?>
                            <form method="POST" action="<?= base_url('admin/users/unban/'.$u['id']) ?>">
                                <?= csrf_field() ?><button class="btn btn-sm btn-outline" title="Unban">✅</button>
                            </form>
                            <?php endif; ?>
                            <form method="POST" action="<?= base_url('admin/users/delete/'.$u['id']) ?>" onsubmit="return confirm('Delete this user permanently?')">
                                <?= csrf_field() ?><button class="btn btn-sm" style="color:var(--danger);background:none;border:1px solid var(--danger);" title="Delete">🗑️</button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include VIEW_PATH . '/layouts/admin_footer.php'; ?>

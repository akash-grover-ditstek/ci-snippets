<div class="container list">
    <div class="row list-header">
        <div class="col s6 m8 l8">
            <h1>Client List <a href="<?= base_url('/clients/create') ?>"><img class="add client" src="<?= base_url('assets/img/icon.png') ?>" /></a></h1>
        </div>
        <div class="col s6 m4 l4 search">
            <input id="table-search" data-table="clients-table" placeholder="Search Clients">
        </div>
    </div>

    <?php $hide_account = true; ?>
    <?php if(is_any_granted([UserRole::ROLE_SUPER_ADMINISTRATOR, UserRole::ROLE_DEV])): ?>
        <?php $hide_account = false;?>
    <?php endif; ?>

    <input type="hidden" value="<?= base64_encode($hide_account); ?>" id="hide-account"/>

    <div class="table_wrap">
        <table id="clients-table" class="display">
            <thead>
                <tr>
                    <td><span>Name</span></td>
                    <td><span>Company</span></td>
                    <td class="no-sort"><span>Edit</span></td>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</div>

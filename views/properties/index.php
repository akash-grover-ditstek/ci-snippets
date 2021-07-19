<div class="container list">
    <div>

        <div class="row list-header">
            <div class="col s6 m8 l8">
                <h1 class="uppercase">
                    <?= get_title() ?> <a href="<?= base_url('/properties/overview/') ?>" class="button-collapse"><img
                                class="add properties" src="<?= base_url('assets/img/icon.png') ?>"/></a>

                </h1>
            </div>
            <div class="col s6 m4 l4 search">
                <input id="table-search" data-table="properties-table" placeholder="Search Listings">
            </div>
        </div>
        <div class="table_wrap">
            <table id="properties-table" class="table display" cellspacing="0" width="100%">
                <thead>
                    <tr>
                        <td><span>Last Updated</span></td>
                        <td><span>Name</span></td>
                        <td><span>Address</span></td>
                        <td class="no-sort"><span>Type</span></td>
                        <td><span>Asking Price</span></td>
                        <td><span>Date Listed</span></td>
                        <td><span>Expiration</span></td>
                        <td><span>Edit</span></td>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>

        <?php if(is_any_granted([UserRole::ROLE_SUPER_ADMINISTRATOR, UserRole::ROLE_DEV]) && isset($import_status) && $import_status->getValue() == 'true'): ?>
            <div class="row no-margin-left">
                <div class="col m12">
                    <a href="<?= base_url('/properties/import') ?>" class="btn btn-primary"><i class="material-icons left">file_upload</i>Import Properties</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<div id="listings-map" data-icon="<?= base_url('/assets/img/marker.png'); ?>"></div>
<?php include "show.php" ?>
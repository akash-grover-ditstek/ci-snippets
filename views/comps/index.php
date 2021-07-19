<style>
    .input-field {
        margin-top: 0;
    }
</style>

<div class="container list">
    <div class="side-nav row" id="slide-out">
        <?php $this->load->view('comps/_partials/form'); ?>
    </div>

    <div>
        <div class="row">
            <div class="col s6 m4 l5">
                <h1 class="uppercase"><?= get_title() ?> List <a data-activates="slide-out" class="button-collapse"
                                                                 onclick="CompsController.openForm()"><img
                                class="add comps" src="<?= base_url('assets/img/icon.png') ?>"/></a>
                </h1>
            </div>
            <div class="col s6 m4 l3 search_comp">
                <ul class="radio">
                    <span>
                        <?= form_radio(array(
                            'name'          => 'lease_sale',
                            'id'            => 'lease_comps',
                            'value'         => 'lease_comps',
                            'class'         => 'select_lease_sale'
                        )); ?>
                        <label for="lease_comps">Leases</label>
                    </span>
                    <span>
                        <?= form_radio(array(
                            'name'          => 'lease_sale',
                            'id'            => 'sale_comps',
                            'value'         => 'sale_comps',
                            'checked'       => TRUE,
                            'class'         => 'select_lease_sale'
                        )); ?>
                        <label for="sale_comps">Sales</label>
                    </span>
                </ul>
            </div>
            <div class="col s6 m4 l4 search">
                <input id="table-search" class="table-search_field comp_table" data-table="comps-table" placeholder="Search Sales Comps">
                <input id="table-search" class="table-search_field lease_table" data-table="lease-table" placeholder="Search Lease Comps" hidden>
            </div>
        </div>
        <div class="table_wrap comp_table">
            <table id="comps-table" class="table display" cellspacing="0" width="100%">
                <thead>
                <tr>
                    <td><span>Last Updated</span></td>
                    <td><span>Name</span></td>
                    <td><span>Sale Price</span></td>
                    <td><span>Building Size</span></td>
                    <td><span>Land Size</span></td>
                    <td><span>$ / SF</span></td>
                    <td><span>Type</span></td>
                    <td><span>Date Sold</span></td>
                    <td class="no-sort"><span>Edit</span></td>
                </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
        <div class="table_wrap lease_table" hidden>
            <table id="lease-table" class="table display" cellspacing="0" width="100%">
                <thead>
                    <tr>
                        <td><span>Last Updated</span></td>
                        <td><span>Name</span></td>
                        <td><span>$/SF/YR</span></td>
                        <td><span>Lease Type</span></td>
                        <td><span>Term (Months)</span></td>
                        <td><span>Building Size</span></td>
                        <td><span>Type</span></td>
                        <td class="no-sort"><span>Edit</span></td>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
</div>


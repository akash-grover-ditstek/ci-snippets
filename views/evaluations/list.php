<style>
    .input-field {
        margin-top: 0;
    }
</style>

<div class="container list">
    <div>
        <div class="row list-header">
            <div class="col s6 m8 l8">
                <h1 class="uppercase"><?= get_title() ?>
                    <a href="#create-evaluation" class="modal-trigger button-collapse">
                        <img class="add evals" src="<?= base_url('assets/img/icon.png') ?>"/>
                        </a>
                </h1>
            </div>
            <div class="col s6 m4 l4 search">
                <input id="table-search" data-table="evals-table" placeholder="Search Evaluations">
            </div>
        </div>
        <div class="table_wrap">
            <table id="evals-table" class="table display" cellspacing="0" width="100%">
                <thead>
                <tr>
                    <td><span>Name</span></td>
                    <td><span>Client</span></td>
                    <td><span>Address</span></td>
                    <td><span>Date Generated</span></td>
                    <td><span>Options</span></td>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($evaluations as $eval) :
                    $this->load->view('evaluations/_partials/tr', $eval);
                endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="create-evaluation" class="modal">
    <div class="modal-content">
        <div class="row">
            <div class="col s12">
                <i class="material-icons dp48 modal-action modal-close modal-close-hyperlink">close</i>
            </div>
        </div>

        <div class="row">
            <div class="col s12">
                <p class="title">Create New Evaluation</p>
            </div>
        </div>

        <div class="row">
            <div class="input-field col s12">
                <?php echo form_open(base_url('evaluations/createevaluation')); ?>
                <?= form_dropdown('clients', $clients, null,
                    'required="required" id="clients" class="browser-default select2"'); ?>
            </div>
        </div>

        <div class="row">
            <div class="col s12">
                <p class="or">OR</p>
            </div>
        </div>

        <div class="row">
            <div class="col s12">
                <p class="link">
                    <a href="<?= base_url('/clients/create?redirect=evaluations&action=create-evaluation') ?>"
                       class="btn btn-raised">Create a New Client</a>
                    <button type="submit" class="hide btn btn-dialog-forward btn-primary waves-effect clients-next">Next</button>
                </p>
            </div>
        </div>
        <?php echo form_close(); ?>
    </div>
</div>

<script>
    $(document).ready(function () {
        if ($.query.get('action') === 'create-evaluation') {
            $('#create-evaluation').modal('open');
        }

        if ($.query.get('client_id')) {
            $('#create-evaluation').find('select[name=clients]').val($.query.get('client_id')).trigger('change');
            $('#create-evaluation').find('select[name=clients]').material_select();
        }
    });
</script>
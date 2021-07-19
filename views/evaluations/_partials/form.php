<div class="container">
    <div class="col s12">
        <div class="row">
            <div class="col s11">
                <h5 class="uppercase"><i id="action"></i> Evaluation</h5>
            </div>

            <div class="col s1 right-align">
                <h5><a href="#" onclick="EvaluationController.closeForm()">X</a></h5>
            </div>
        </div>
    </div>

    <form id="evaluation-short-form" class="col s12" enctype="multipart/form-data" method="post">
        <?= form_input([
                'name' => 'evaluation[id]',
                'id' => 'id',
                'type' => 'hidden'
        ]); ?>

        <div class="row comps-image-container">
            <img id="image" class="hide">
            <div class="file-field input-field comps-image center-align">
                <div class="add-photo">
                    <div>
                        <img class="add-icon" src="<?= base_url('assets/img/icon.png') ?>"/>
                        <span>Add A Property Image</span>
                        <input type="file" name="photo" accept=".png, .jpg, .jpeg">
                    </div>
                </div>
                <div class="file-path-wrapper">
                    <?= form_input([
                        'name' => 'photo',
                        'class' => 'hide file-path validate'
                    ]); ?>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="input-field col s12">
                <?= form_label('Property Name', "evaluation[business_name]") ?>
                <?= form_input([
                    'name' => 'evaluation[business_name]',
                    'autocomplete' => 'random']
                    ); ?>
            </div>
        </div>

        <div class="row">
            <div class="input-field col s12 required">
                <?= form_label('Street Address', "evaluation[street_address]") ?>
                <?= form_input([
                    'name' => 'evaluation[street_address]',
                    'id' => 'route',
                    'required' => 'required',
                    'onfocus' => 'Geolocate.search()',
                    'autocomplete' => 'random',
                    'placeholder' => "",
                    'disabled' => 'disabled'
                ]); ?>
            </div>
        </div>

        <div class="row">
            <div class="input-field col s4 required">
                <?= form_label('City', "evaluation[city]") ?>
                <?= form_input([
                    'name' => 'evaluation[city]',
                    'id' => 'locality',
                    'required' => 'required',
                    'disabled' => 'disabled'
                ]); ?>
            </div>

            <div class="input-field col s5 required">
                <?= form_dropdown('evaluation[state]', get_states(), '',
                    '
                    required="required" 
                    id="administrative_area_level_1" 
                    disabled="disabled"
                    '); ?>
                <?= form_label('State', 'states') ?>
            </div>

            <div class="input-field col s3 required">
                <?= form_label('Zipcode', "evaluation[zipcode]") ?>
                <?= form_input([
                    'name' => 'evaluation[zipcode]',
                    'id' => 'postal_code',
                    'maxlength' => '5',
                    'class' => 'zipcode',
                    'required' => 'required',
                    'disabled' => 'disabled'
                ]); ?>
            </div>

            <div class="input-field col s3 hide">
                <?= form_label('County', "evaluation[county]") ?>
                <?= form_input([
                    'name' => 'evaluation[county]',
                    'id' => 'administrative_area_level_2',
                    'disabled' => 'disabled'
                ]); ?>
            </div>
        </div>

        <div class="row">
            <div class="input-field col s6">
                <?= mat_select('Type', 'evaluation[type]', get_evaluation_type(), null,
                    'required="required" name="evaluation[type]"'); ?>
            </div>
            <div class="input-field col s6" data-eval-type="sale">
                <?= form_label('Under Contract Price', 'evaluation[under_contract_price]') ?>
                <?= form_input([
                    'name' => 'evaluation[under_contract_price]',
                    'class' => 'currency'
                ]); ?>
            </div>
            <div class="input-field col s3" data-eval-type="non-sale">
                <?= form_label('Last Transferred Date', 'evaluation[last_transferred_date]') ?>
                <?= form_input([
                    'name' => 'evaluation[last_transferred_date]',
                    'type' => 'text',
                    'class' => 'standalone-datepicker',
                    'data-datepicker-max' => 'today',
                ]); ?>
            </div>
            <div class="input-field col s3" data-eval-type="non-sale">
                <?= form_label('Price', 'evaluation[price]') ?>
                <?= form_input([
                    'name' => 'evaluation[price]',
                    'class' => 'currency'
                ]); ?>
            </div>
        </div>

        <div class="row">
            <div class="input-field col s6">
                <?= form_label('Land Size',  'evaluation[land_size]') ?>
                <?= form_input([
                        'name' => 'evaluation[land_size]',
                        'class' => 'evallandsize'
                ]); ?>
            </div>

            <div class="input-field col s6">
                <?= mat_select('Dimensions',
                    'evaluation[land_dimension]',
                    get_dimensions(),
                    null,
                    'name="evaluation[land_dimension]" class="evallanddimension"'); ?>
            </div>
        </div>

        <div class="row">
            <div class="input-field col s6">
                <?= form_label('Building Size (SF)', 'evaluation[building_size]') ?>
                <?= form_input([
                    'name' => 'evaluation[building_size]',
                    'class' => 'area'
                ]); ?>
            </div>
            <div class="input-field col s3">
                <?= form_label('Built Year', 'evaluation[year_built]') ?>
                <?= form_input([
                    'name' => 'evaluation[year_built]',
                    'class'=>'postmask-year',
                    'min' => 1800,
                    'max' => date('Y'),
                    'maxlength' => 4
                ]); ?>
            </div>
            <div class="input-field col s3">
                <?= form_label('Remodeled Year', 'evaluation[year_remodeled]') ?>
                <?= form_input([
                    'name' => 'evaluation[year_remodeled]',
                    'type' => 'number'
                ]); ?>
            </div>
        </div>

        <div class="row">
            <div class="input-field col s12">
                <?= form_label('Zoning Type', 'evaluation[zoning_type]') ?>
                <?= form_input([
                        'name' => 'evaluation[zoning_type]'
                ]); ?>
            </div>
        </div>

        <div class="row">
            <div class="input-field col s12">
                <?= form_dropdown(
                    'evaluation[frontage]',
                    get_frontages(),
                    null,
                    'name="evaluation[frontage]"'
                ); ?>
                <?= form_label('Frontage', 'evaluation[frontage]') ?>
            </div>
        </div>

        <div class="row">
            <div class="input-field col s6">
                <?= form_button([
                    'type' => 'button',
                    'class' => 'btn btn-primary full-width-button',
                    'content' => 'Update',
                    'id' => 'save-evaluation'
                ]); ?>
            </div>
        </div>
    </form>
</div>
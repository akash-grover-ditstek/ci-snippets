<style>
    .input-with-calendar-icon {
        position: absolute;
        top: 10px;
        right: 10px;
        z-index: 3;
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        user-select: none;
    }
</style>

<div class="container">
    <div class="col s12">
        <div class="row">
            <div class="col s11">
                <h5 class="uppercase"><i id="action"></i> Comp</h5>
            </div>

            <div class="col s1 right-align">
                <h5><a href="#" onclick="CompsController.closeForm()">X</a></h5>
            </div>
        </div>
    </div>

    <form id="comps-form" class="col s12" enctype="multipart/form-data" method="post">

        <?= form_input(array('name' => 'id', 'id' => 'id', 'type' => 'hidden')); ?>
        <?= form_input(array('name' => 'addToEvaluation', 'id' => 'addToEvaluation', 'type' => 'hidden')); ?>

        <div class="row comps-image-container">
            <img id="image" class="hide">
            <div class="file-field input-field comps-image center-align">
                <div class="add-photo">
                    <div>
                        <img class="add-icon" src="<?= base_url('assets/img/icon.png') ?>"/>
                        <span>Add A Property Image</span>
                        <input type="file" name="property_image_url" accept=".png, .jpg, .jpeg">
                    </div>
                </div>
                <div class="file-path-wrapper">
                    <?= form_input(array('name' => 'property_image_url', 'class' => 'hide file-path validate')); ?>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="input-field col s12">
                <?= form_label('Property Name', "business_name") ?>
                <?= form_input(array('name' => 'business_name', 'autocomplete' => 'random')); ?>
            </div>
        </div>

        <div class="row">
            <div class="input-field col s6 required">
                <?= form_label('Street Address', "street_address") ?>
                <?= form_input(array(
                    'name' => 'street_address',
                    'id' => 'route',
                    'required' => 'required',
                    'onfocus' => 'Geolocate.search()',
                    'autocomplete' => 'random',
                    'placeholder' => ""
                )); ?>
            </div>
            <div class="input-field col s6">
                <?= form_label('Suite Number',"street_suite") ?>
                <?= form_input(array(
                    'name' => 'street_suite',
                    'placeholder' => "",
                )); ?>
            </div>
        </div>

        <div class="row">
            <div class="input-field col s4 required">
                <?= form_label('City', "city") ?>
                <?= form_input(array('name' => 'city', 'id' => 'locality', 'required' => 'required')); ?>
            </div>

            <div class="input-field col s5 required">
                <?= form_dropdown('state', get_states(), '', 'required="required" id="administrative_area_level_1"'); ?>
                <?= form_label('State', 'states') ?>
            </div>

            <div class="input-field col s3 required">
                <?= form_label('Zipcode', "zipcode") ?>
                <?= form_input(array('name' => 'zipcode', 'class' => 'zipcode', 'required' => 'required','maxlength'=>'5', 'id'=>'postal_code')); ?>
            </div>

            <div class="input-field col s3 hide">
                <?= form_label('County', "county") ?>
                <?= form_input(array('name' => 'county', 'id' => 'administrative_area_level_2')); ?>
            </div>
        </div>

        <div id="map-pin-wrapper" class="comps">
            <?= form_input(array('name' => 'latitude', 'type' => 'hidden')); ?>
            <?= form_input(array('name' => 'map_pin_lat', 'type' => 'hidden')); ?>
            <?= form_input(array('name' => 'longitude', 'type' => 'hidden')); ?>
            <?= form_input(array('name' => 'map_pin_lng', 'type' => 'hidden')); ?>
            <?= form_input(array('name' => 'map_pin_zoom', 'type' => 'hidden')); ?>
            <div id="map-pin">
                <!-- The map from Google Maps will be loaded here. -->
            </div>
        </div>

        <div class="row">
            <div class="input-field col s12">
                <?= form_label('Property Summary', "summary") ?>
                <?= form_textarea(array('name' => 'summary', 'class' => 'materialize-textarea')); ?>
            </div>
        </div>
        <div class="row">
            <div class="col s12">
                <ul class="radio">
                    <?php 
                        $get_comp_type = get_comp_type();
                        foreach($get_comp_type as $key => $value){
                            ?>
                            <span>
                                <?= form_radio(array(
                                    'name'          => 'comp_type',
                                    'id'            => $key,
                                    'value'         => $key,
                                    'onclick'       => 'setPriceSquareFoot()',
                                    'class'         => 'select_comp_type'
                                )); ?>
                                <label for="<?= $key; ?>"><?= $value; ?></label>
                            </span>
                        <?php } 
                    ?>
                </ul>
            </div>  
        </div></br>
        <div class="row zoning_block">
        <?php $this->load->view('_partials/zoning.php'); ?>
        </div>

        <div class="row">
            <div class="col s4 hide select_land_type">
                <div class="row enable-type-own land-type-option">
                    <div class="input-field col required s6">
                        <?= mat_select('Land Type', "land_type", get_land_type(null), '',
                            'class="land_type" onchange="change_custom_field(this,`land-type-option`,`land_type_custom`,`land`)"'); ?>
                    </div>
                    <div class="input-field col s6 type-own-field">
                        <input type="text" class="type_own_custom" placeholder="Other Land Type" id="land_type_custom" name="land_type_custom">
                    </div>
                </div>
            </div>
            <div class="input-field col s6 land_type_field">
                <?= form_label('Land Size', "land_size") ?>
                <?= form_input(array('name' => 'land_size', 'class' => 'evallandsize postmask-integer','onblur'=>'setPriceSquareFoot()')); ?>

            </div>
            <div class="input-field col s6 land_type_field">
                <?= mat_select('Size Type', 'land_dimension', get_dimensions(), null, 'name="land_dimension" class="evallanddimension"'); ?>
            </div>
        </div>

        <div class="row">
            <div class="input-field col s6 required">
                <?= form_dropdown('type', get_comp_types(), '', 'required="required" id="type"'); ?>
                <?= form_label('Type', ",") ?>
            </div>
            <div class="input-field col s6 required">
                <?= form_dropdown('condition', get_conditions(), '', 'required="required" id="conditions"'); ?>
                <?= form_label('Condition', "condition") ?>
            </div>
        </div>

        <div class="row">
            <div class="input-field col s6 hide-if-land" hidden>
                <?= form_label('Building Size (SF)', "building_size") ?>
                <?= form_input(array('name' => 'building_size', 'class' => 'postmask-integer','onblur'=>'setPriceSquareFoot()','required' => 'required')); ?>

            </div>
            <div class="input-field col s6 hide-if-land">
                <?= form_label('Built Year', "year_built") ?>
                <?= form_input(array('name' => 'year_built', 
                                    'class' => 'postmask-year',
                                    'min' => 1800,
                                    'max' => date('Y'),
                                    'maxlength' => 4)); ?>
            </div>
            <div class="input-field col s6 hide-if-land">
                <?= form_label('Remodeled Year', "year_remodeled") ?>
                <?= form_input(array('name' => 'year_remodeled' ,'class' => 'postmask-year',
                                    'min' => 1800,
                                    'max' => date('Y'),
                                    'maxlength' => 4)); ?>
            </div>
        </div>
        <div class="row">
            <div data-ref="sale">
                <div class="input-field col s6 required">
                    <?= form_label('Sale Price', "sale_price") ?>
                    <?= form_input(array('name' => 'sale_price', 'class' => 'postmask-currency','onblur'=>'setPriceSquareFoot()','required' => 'required')); ?>
                </div>
            </div>
            <div class="input-field col s6 required" data-ref="lease">
                <?= mat_select('Lease Type', 'lease_type', get_lease_types(), null, 'name="lease_type" '); ?>
            </div>

            <div>
                <div class="input-field col s6 required">
                    <?= form_label('Transaction Date', "date_sold",'data-ref="lease"') ?>
                    <?= form_label('Date Sold', "date_sold",'data-ref="sale"') ?>
                    <?= form_input(array(
                        'name' => 'date_sold',
                        'type' => 'text',
                        'class' => 'standalone-datepicker',
                        'data-language' => 'en',
                        'required' => 'required'
                    )); ?>
                    <i class="material-icons input-with-calendar-icon">date_range</i>
                </div>
            </div>
        </div>
        <div class="row" data-ref="sale">
            <div class="input-field col s6">
                <?= form_label('Net Operating Income (NOI)', "net_operating_income") ?>
                <?= form_input(array('name' => 'net_operating_income', 'class' => 'postmask-currency')); ?>
            </div>        
            <div class="input-field col s6">
                <?= form_label('CAP Rate', "cap_rate") ?>
                <?= form_input(array('name' => 'cap_rate','class' => 'postmask-percentage')); ?>
            </div>
        </div>    
        <div class="row">
            <div class="input-field col s12">
                <?= form_label('Zoning Type', 'zoning_type') ?>
                <?= form_input([
                    'name' => 'zoning_type'
                ]); ?>
            </div>
        </div>

        <div class="row">
            <div class="input-field col s12">
                <?= mat_select('Frontage', 'frontage', get_frontages(), null, 'name="frontage"'); ?>
            </div>
        </div>


        <div class="row" data-ref="lease">
            <div class="input-field col s4">
                <?= form_label('Lease Rate', "lease_rate") ?>
                <?= form_input(array('name' => 'lease_rate','class'=>'postmask-integer')); ?>
            </div>

            <div class="input-field col s4">
                <?= form_label('$ / SF', "price_square_foot") ?>
                <?= form_input(array('name' => 'price_square_foot', 'class' => 'postmask-currency')); ?>
            </div>

            <div class="input-field col s4">
                <?= form_label('Term (Months)', "term") ?>
                <?= form_input(array('name' => 'term', 'class' => 'postmask-term')); ?>
            </div>
        </div>

        <div class="row" data-ref="lease">
            <div class="input-field col s12">
                <?= form_label('Concessions', "concessions") ?>
                <?= form_textarea(array('name' => 'concessions', 'class' => 'materialize-textarea')); ?>
            </div>
        </div>

        <div class="row">
            <div class="input-field col s12 m6 l6">
                <?= mat_select('Utilities', 'utilities_select', get_utilities(),
                    null,
                    'name="utilities_select" id="utilities_select"'); ?>
            </div>
            <div class="input-field col s12 m6 l6 hide">
                <?= form_input(array(
                    'name' => 'utilities_text',
                    'id' => 'utilities_other',
                    'placeholder' => 'Other utilities'
                )); ?>
            </div>
        </div>


        <div class="row">
            <div class="input-field col s6">
                <?= form_button(array(
                    'type' => 'button',
                    'class' => 'btn btn-primary full-width-button',
                    'content' => 'Create',
                    'id' => 'save-comp'
                )) ?>
            </div>

            <?php if (!isset($isEval)) : ?>
                <div class="input-field col s6">
                    <?= form_button(array(
                        'type' => 'button',
                        'class' => 'btn btn-grey delete-comp full-width-button',
                        'content' => 'Delete',
                        'id' => 'delete-comp'
                    )) ?>
                </div>
            <?php endif; ?>
        </div>
    </form>
</div>

<script>
    function initCompsAutocompleteSearch() {
        window.Geolocate.autocomplete();
        MapController.CompsPin.init();
        MapController.CompsPin.setSelectedPin();
    }
	
    $("select[name='zonings[index][zone]']").change(function () {
        setPriceSquareFoot();
    });

    function checkRequiedFields()
    {
        var type=$("select[name='type']").val();
        var property_type=$("select[name='zonings[index][zone]']").val();
        if(type==="sale" && property_type==='land')
        {
            $("[name=sale_price]").prop('required',true);
            $("[name=building_size]").prop('required',false);
            $('.space').each(function() { 
                $(this).prop('required',false);
            });
        }else if(type==="lease" && property_type==='land')
        {
            $("[name=sale_price]").prop('required',false);
            $("[name=building_size]").prop('required',false);
            
            $('.space').each(function() { 
                $(this).prop('required',false);
            });
        }else if(type==="sale" && property_type!=='land')
        {
            $("[name=sale_price]").prop('required',true);
            $("[name=building_size]").prop('required',true);
            
            $('.space').each(function() { 
                $(this).prop('required',true);
            });
        }else if(type==="lease" && property_type!=='land')
        {
            $("[name=sale_price]").prop('required',false);
            $("[name=building_size]").prop('required',true);
            
            $('.space').each(function() { 
                $(this).prop('required',true);
            });
        }
    }

    function setPriceSquareFoot()
    {
        var type=$("select[name='type']").val();
        var comp_type=$('input[type="radio"]:checked').val();

        if($("[name=sale_price]").val() !== "") $("[name=sale_price]").maskMoney('mask');
        var sale_price = $("[name=sale_price]").val().replace(".00", "").replace(/[^0-9]/gi, '');
        var building_size = $("[name=building_size]").val().replace(/[^0-9]/gi, '');
        var land_size = $("[name=land_size]").val().replace(/[^0-9]/gi, '');
        if(sale_price=="")
        {
            sale_price=0;
        }
        if(building_size=="")
        {
            building_size=1;
        }
        if(land_size=="")
        {
            land_size=1;
        }
        if(type==="sale" && comp_type!=='land_only')
        {
            var price_square_foot=(parseFloat(sale_price)/parseFloat(building_size)).toFixed(2);
        }else if(type==="sale")
        {
            var price_square_foot=(parseFloat(sale_price)/parseFloat(land_size)).toFixed(2);
        }else
        {
          var price_square_foot=$("[name=price_square_foot]").val();
        }
        $("[name=price_square_foot]").val(price_square_foot);
        $("[name=price_square_foot]").maskMoney('mask');
        $("[name=price_square_foot]").prev().addClass('active');
    }
</script>
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBNuq_P-RYH4b6sS_8suJRt-1glUudOIa0&libraries=drawing,geometry,places&callback=initCompsAutocompleteSearch"
        async defer></script>
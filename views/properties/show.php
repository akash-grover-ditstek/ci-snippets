<div class="drawer">
    <aside id="property-detail" class="property-detail">

        <div class="filter z-index-0">
            <a href="#" class="step btn btn-primary right" onclick="PropertiesController.closeDetails()">Back to Listings</a>
        </div>

        <section class="content row">
            <div class="col l7 m7 s12">

                <div class="row heading">
                    <div class="col l12 m12 s12">
                        <h1>
                            <span data-ref="street_address"></span>
                            <small>
                                <span data-ref="city"></span>, <span data-ref="state"></span> <span
                                        data-ref="zipcode"></span>
                            </small>
                            <span class="listed" data-ref="date_listed">Listed 5 days ago</span>
                        </h1>
                        <h2 class="right">
                            <small>Asking</small>
                            <span data-ref="asking_price"></span>
                        </h2>
                    </div>
                </div>

                <div class="row">
                    <div class="col  l6 m6 s12">
                        <div class="property">
                            <h3><span data-ref="business_name"></span></h3>
                            <div class="property-status">
                                <p>Under Contract</p>
                            </div>
                            <dl>
                                <dt>Property Type</dt>
                                <dd data-ref="zoning_type"></dd>
                                <dt>Building Class</dt>
                                <dd data-ref="property_class"></dd>
                                <dt>Offering Type</dt>
                                <dd data-ref="sale_type"></dd>
                                <dt>Lot Size</dt>
                                <dd data-ref="land_size"></dd>
                                <dt>Available SF</dt>
                                <dd data-ref="building_size"></dd>
                                <dt>No. Stories</dt>
                                <dd data-ref="n_stories"></dd>
                                <dt>Year Built</dt>
                                <dd data-ref="year_built"></dd>
                                <dt>Parking Ratio</dt>
                                <dd data-ref="parking"></dd>
                                <dt>Zoning Description</dt>
                                <dd data-ref="zoning_description"></dd>
                                <dt>APN / Parcel ID</dt>
                                <dd data-ref="apn"></dd>
                                <dt>Main Broker</dt>
                                <dd data-ref="main_broker"></dd>
                                <dt>Secondary Brokers</dt>
                                <dd data-ref="secondary_brokers"></dd>
                            </dl>
                        </div>

                        <a href="#" id="link-to-edit-page"><button type="button" class="btn btn-primary right">Edit Listing</button></a>
                    </div>

                    <div class="col l6 m6 s12">
                        <div class="description">
                            <h4>Property Description</h4>
                            <p data-ref="description"></p>
                        </div>
                        <div class="notes">
                            <h4>Sales Notes</h4>
                            <p data-ref="notes"></p>
                        </div>
                        <div class="attachments">
                            <h4>Attachments</h4>
                            <div data-ref="attachments">

                            </div>
                        </div>
                    </div>
                </div>

                <div data-ref="suites-parcels" class="units hide">
                    <h4 data-ref="suites-parcels-title"></h4>
                    <ul class="row">
                        <li data-ref="unit-clone" class="hide col l6 m6 s12">
                            <div>
                                <h5 data-ref="unit-title"></h5>
                                <dl>
                                    <dd>Lease Rate</dd>
                                    <dt data-ref="unit-lease-rate"></dt>
                                    <dd><span data-ref="unit-type-title"></span> Size</dd>
                                    <dt><span data-ref="unit-size"></span> <span data-ref="unit-dimension"></span></dt>
                                    <dd>Lease Type</dd>
                                    <dt data-ref="unit-lease-type"></dt>
                                    <dd>Floor</dd>
                                    <dt data-ref="unit-floor"></dt>
                                    <dd>Description</dd>
                                    <dt data-ref="unit-description"></dt>
                                </dl>
                            </div>

                            <div class="unit-deal-status">
                                <dl>
                                    <dt data-ref="unit-deal-status"></dt>
                                </dl>
                            </div>
                        </li>
                    </ul>
                    <p data-ref="suites-parcels-val"></p>
                </div>

            </div>

            <div class="col l5 m5 s12">
                <div class="gallery">
                    <div class="carousel carousel-slider" data-ref="gallery">
                        <div class="arrow left">
                            <a href="previous"
                               class="movePrevCarousel middle-indicator-text waves-effect waves-light content-indicator"><i
                                        class="material-icons left  middle-indicator-text">chevron_left</i></a>
                        </div>

                        <div class="arrow right">
                            <a href="next"
                               class=" moveNextCarousel middle-indicator-text waves-effect waves-light content-indicator"><i
                                        class="material-icons right middle-indicator-text">chevron_right</i></a>
                        </div>
                    </div>
                </div>

                <div class="boundaries">
                    <div class="map" style="background-image: url(<?= base_url('assets/img/no-photo-available.png')?>)"
                         data-ref="map_image_url"></div>
                </div>
            </div>
        </section>
    </aside>
</div>
<script src="./ProductJs.js"></script>
<template>
    <div class="columns" >


        <div class="column" v-if="data && data.item">

            <div class="card">
                <div class="card-header">

                    <div class="card-header-buttons has-margin-left-5">

                        <div class="field has-addons is-pulled-right">

                            <p class="control">
                                <b-button icon-left="arrow-left"
                                          type="is-light"
                                          @click="resetItem()">
                                </b-button>

                            </p>

                            <p class="control">
                                <b-button @click="$vaah.copy(data.item.id)"  type="is-light">
                                    <small><b>#{{data.item.id}}</b></small>
                                </b-button>
                            </p>



                        </div>


                    </div>


                    <div class="card-header-title">

                        {{data.item.name}}

                    </div>

                </div>

                <div class="card-content">
                    <b-tabs vertical>
                        <b-tab-item label="Variation">
                            Lorem ipsum dolor sit amet.
                        </b-tab-item>

                        <b-tab-item label="Attributes">
                            Lorem <br>
                            ipsum <br>
                            dolor <br>
                            sit <br>
                            amet.
                        </b-tab-item>

                        <b-tab-item  label="Books">
                            What light is light, if Silvia be not seen? <br>
                            What joy is joy, if Silvia be not by— <br>
                            Unless it be to think that she is by <br>
                            And feed upon the shadow of perfection? <br>
                            Except I be by Silvia in the night, <br>
                            There is no music in the nightingale.
                        </b-tab-item>

                        <b-tab-item label="Videos" >
                            Nunc nec velit nec libero vestibulum eleifend.<br>
                            Curabitur pulvinar congue luctus.
                            Nullam hendrerit iaculis augue vitae ornare.
                            Maecenas vehicula pulvinar tellus, id sodales felis lobortis eget.
                        </b-tab-item>
                    </b-tabs>
                </div>

            </div>

        </div>

    </div>
</template>



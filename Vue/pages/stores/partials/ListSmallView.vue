<script src="./ListSmallViewJs.js"></script>
<template>
    <div>
        <div v-if="data && data.list">
            <b-table :data="data.list.length ? [] : data.list.data"
                     :checked-rows.sync="data.actions.items"
                     :hoverable="true"
                     :row-class="setRowClass">

                <template >

                    <b-table-column field="name" label="Name" v-slot="props">
                        {{ props.row.name }}
                    </b-table-column>

                    <b-table-column field="actions" label=""
                                    v-slot="props"
                                    width="40">

                        <b-tooltip label="View" type="is-dark">
                            <b-button size="is-small"
                                      @click="setActiveItem(props.row)"
                                      icon-left="chevron-right">
                            </b-button>
                        </b-tooltip>


                    </b-table-column>


                </template>

                <template slot="empty">
                    <section class="section">
                        <div class="content has-text-grey has-text-centered">
                            <p>Nothing here.</p>
                        </div>
                    </section>
                </template>

            </b-table>
        </div>
    </div>

</template>

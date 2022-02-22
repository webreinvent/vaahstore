<script src="./ListSmallViewJs.js"></script>
<template>
    <div>
        <b-table :data="page.list_is_empty ? [] : page.list.data"
                 :checked-rows.sync="page.bulk_action.selected_items"
                 checkbox-position="left"
                 :hoverable="true"
                 :row-class="setRowClass"
        >

            <template >
                <b-table-column field="id" label="ID" width="40" numeric v-slot="props">
                    {{ props.row.id }}
                </b-table-column>

                <b-table-column field="name" label="Name" v-slot="props">
                    {{ $vaah.limitString(props.row.name, 15) }}
                </b-table-column>

                <b-table-column field="slug" label="Slug" v-slot="props">
                    <vh-copy class="text-copyable"
                             :data="props.row.slug"
                             :label="$vaah.limitString(props.row.slug, 15)"
                             @copied="copiedData"
                    >
                    </vh-copy>
                </b-table-column>


                <b-table-column field="actions" label=""
                                width="40" v-slot="props">
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
</template>

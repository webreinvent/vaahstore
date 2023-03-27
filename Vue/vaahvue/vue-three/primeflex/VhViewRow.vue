<script setup>
import {vaah} from '../../pinia/vaah.js'

const props = defineProps({
    label: {
        type: String,
        default: null
    },
    label_width: {
        type: String,
        default: '150px'
    },
    value:{
        default: null,
    },
    type: {
        type: String,
        default: 'text'
    },
    can_copy:{
        type: Boolean,
        default: false
    }
})

//['label', 'value', 'type']

</script>
<template>
    <tr>
        <td :style="{width: label_width}"><b>{{vaah().toLabel(label)}}</b></td>
        <template v-if="can_copy">
            <td>{{value}}</td>
            <td style="width: 40px;">
                <Button icon="pi pi-copy" @click="vaah().copy(value)" class=" p-button-text"></Button>
            </td>
        </template>
        <template v-else-if="type==='user'">
            <td colspan="2" >

                <template v-if="typeof value === 'object' && value !== null">
                    <Button  @click="vaah().copy(value.id)"  class="p-button-outlined p-button-secondary p-button-sm">
                        {{value.name}}
                    </Button>
                </template>

            </td>
        </template>

        <template v-else-if="type==='status'">
            <td colspan="2" >
                <template v-if="typeof value === 'object' && value !== null">
                    <Tag v-if="value.slug == 'approved'"  @click="vaah().copy(value.id)" severity="success">
                        {{value.name}}
                    </Tag>
                    <Tag v-else-if="value.slug == 'pending'" @click="vaah().copy(value.id)" severity="warning">
                        {{value.name}}
                    </Tag>
                    <Tag v-else-if="value.slug == 'rejected'" @click="vaah().copy(value.id)" severity="danger">
                        {{value.name}}
                    </Tag>
                    <Tag v-else @click="vaah().copy(value.id)" severity="primary">
                        {{value.name}}
                    </Tag>
                </template>
            </td>
        </template>

        <template v-else-if="type==='multipleCurrency'">
            <td colspan="2" >
                <template v-if="typeof value === 'object' && value && value.length > 0">
                    <span v-for="data in value">
                        {{data.code}}<b>({{data.symbol}})</b>
                        <span v-if="data.is_default == 1">
                            <badge value="Default" severity="Default"></badge>
                        </span>&nbsp;&nbsp;
                    </span>
                </template>
                <template v-else><Tag value="No" severity="danger"></Tag></template>
            </td>
        </template>

        <template v-else-if="type==='defaultCurrency'">
            <td colspan="2" >
                <template v-if="typeof value === 'object' && value !== null">
                    <span>
                        {{value.code}}<b>({{value.symbol}})</b>
                     </span>
                </template>
            </td>
        </template>

        <template v-else-if="type==='multipleLingual'">
            <td colspan="2" >
                <template v-if="typeof value === 'object' && value && value.length > 0">
                    <span v-for="data in value">
                        {{data.name}}&nbsp; &nbsp;
                    </span>
                </template>
                <template v-else><Tag value="No" severity="danger"></Tag></template>
            </td>
        </template>

        <template v-else-if="type==='defaultLingual'">
            <td colspan="2" >
                <template v-if="typeof value === 'object' && value !== null">
                    <span>
                        {{value.name}}
                     </span>
                </template>
            </td>
        </template>

        <template v-else-if="type==='allowedIps'">
            <td colspan="2" >
                <template v-if="typeof value === 'object' && value && value.length > 0">
                    <span v-for="data in value">
                        <badge :value="data" severity="Default"></badge>&nbsp; &nbsp;
                    </span>
                </template>
                <template v-else><Tag value="No" severity="danger"></Tag></template>
            </td>
        </template>

        <template v-else-if="type==='userEmail'">

            <td colspan="2" >

                <template v-if="typeof value === 'object' && value !== null">
                    <Button  @click="vaah().copy(value.email)"  class="p-button-outlined p-button-secondary p-button-sm">
                        {{value.name}}
                    </Button>
                </template>

            </td>
        </template>

        <template v-else-if="type==='attributeValues'">

            <td colspan="2" >

                <template v-if="typeof value === 'object' && value !== null">
                    <span v-for="data in value">
                        <span>
                            {{data.name}}<b>({{data.type}})</b>&nbsp;<br />
                        </span>
                    </span>
                </template>

            </td>
        </template>

        <template v-else-if="type==='yes-no'">
            <td colspan="2">
                <Tag value="Yes" v-if="value===1 || value=='yes'" severity="success"></Tag>
                <Tag v-else value="No" severity="danger"></Tag>
            </td>
        </template>
        <template v-else-if="type==='multipleValues'">
            <td colspan="2">
                <template v-if="typeof value === 'object' && value !== null">
                    <span v-for="data in value">
                        <Button  @click="vaah().copy(data.id)"  class="p-button-outlined p-button-secondary p-button-sm">
                            {{data.value}}
                        </Button>&nbsp;
                    </span>
                </template>
            </td>
        </template>


        <template v-else>
            <td  colspan="2">{{value}}</td>
        </template>

    </tr>
</template>

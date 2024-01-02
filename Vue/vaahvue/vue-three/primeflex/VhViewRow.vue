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
    },
    meta_tags:{
        type:Object,
        default:null,
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
                    <Button  @click="vaah().copy(value.name)"  class="p-button-outlined p-button-secondary p-button-sm">
                        {{value.name}}
                    </Button>
                </template>

            </td>
        </template>

        <template v-else-if="type==='status'">
            <td colspan="2" >
                <template v-if="typeof value === 'object' && value !== null">
                    <Badge v-if="value.slug == 'approved'"  @click="vaah().copy(value.name)" severity="success">
                        {{value.name}}
                    </Badge>
                    <Badge v-else-if="value.slug == 'pending'" @click="vaah().copy(value.name)" severity="warning">
                        {{value.name}}
                    </Badge>
                    <Badge v-else-if="value.slug == 'rejected'" @click="vaah().copy(value.name)" severity="danger">
                        {{value.name}}
                    </Badge>
                    <Badge v-else @click="vaah().copy(value.name)" severity="primary">
                        {{value.name}}
                    </Badge>
                </template>
            </td>
        </template>

        <template v-else-if="type==='multipleCurrency'">
            <td colspan="2" >
                <template v-if="typeof value === 'object' && value && value.length > 0">
                    <span v-for="data in value">
                       <Tag :severity="primary" :value="data.name"  style="margin-top:10px;border-radius:20px;padding:5px 10px;"></Tag>
                        &nbsp;&nbsp;
                    </span>
                </template>
            </td>
        </template>

        <template v-else-if="type==='defaultCurrency'">
            <td colspan="2" >
                <template v-if="typeof value === 'object' && value !== null">
                    <span>
                        <b>{{value.name}}</b>
                     </span>
                </template>
                <template v-else><Tag value="No" severity="danger"></Tag></template>
            </td>
        </template>

        <template v-else-if="type==='multipleLingual'">
            <td colspan="2" >
                <template v-if="typeof value === 'object' && value && value.length > 0">
                    <span v-for="data in value">
                        <Tag :severity="primary" :value="data.name" :rounded="true" style="margin-top:10px;border-radius:20px;padding:5px 10px;"></Tag> &nbsp;
                    </span>
                </template>
            </td>
        </template>

        <template v-else-if="type==='defaultLingual'">
            <td colspan="2" >
                <template v-if="typeof value === 'object' && value !== null">
                    <span>
                        <b>{{value.name}}</b>
                     </span>
                </template>
                <template v-else><Tag value="No" severity="danger"></Tag></template>
            </td>
        </template>

        <template v-else-if="type==='allowedIps'">
            <td colspan="2" >
                <template v-if="typeof value === 'object' && value && value.length > 0">
                    <span v-for="data in value">
                        <Tag :severity="primary" :value="data" :rounded="true" style="border-radius:20px;padding:5px 10px;"></Tag> &nbsp;
                    </span>
                </template>
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

        <template v-else-if="type==='productAttributeValues'">

            <td colspan="2" >

                <template v-if="typeof value === 'object' && value !== null">
                    <table class="table">
                        <tbody>
                            <tr v-for="data in value">
                                <td><div style="word-break: break-word;">{{data.new_value}}</div></td>
                            </tr>
                        </tbody>
                    </table>
                </template>

            </td>
        </template>

        <template v-else-if="type==='image_preview'">

            <td colspan="2" >

                <Image :src="`image/uploads/brands/`+value"
                       preview
                       alt="Image"
                       width="70" />
            </td>
        </template>

        <template v-else-if="type==='yes-no'">
            <td colspan="2">
                <Badge value="Yes" v-if="value===1 || value=='yes'" severity="success"></Badge>
                <Badge v-else value="No" severity="danger"></Badge>
            </td>
        </template>
        <template v-else-if="type==='multipleValues'">
            <td colspan="2">
                <template v-if="typeof value === 'object' && value !== null">
                    <span v-for="data in value">
                        <Button class="p-button-outlined p-button-secondary p-button-sm">
                            {{data.value}}
                        </Button>&nbsp;
                    </span>
                </template>
            </td>
        </template>

<!--        <template v-else-if="type==='meta_tags'">-->
<!--            <td colspan="2">-->
<!--                <span v-for="tag in value ">-->
<!--                    <Button class="p-button-outlined p-button-secondary p-button-sm">-->
<!--                            {{tag}}-->
<!--                        </Button>&nbsp;-->
<!--                </span>-->
<!--            </td>-->
<!--        </template>-->

        <template v-else>
            <td  colspan="2">{{value}}</td>
        </template>

    </tr>
</template>

import {createApp, markRaw} from 'vue';
import { createPinia, PiniaVuePlugin  } from 'pinia'
import PrimeVue from "primevue/config";
import { definePreset } from '@primeuix/themes';
import Aura from '@primeuix/themes/aura';

import VueApexCharts from 'vue3-apexcharts';

import '@/index.css'

//-------------PrimeVue Imports
import BadgeDirective from "primevue/badgedirective";
import ConfirmDialog from 'primevue/confirmdialog';
import ConfirmationService from 'primevue/confirmationservice';
import DialogService from 'primevue/dialogservice'
import Menu from 'primevue/menu';
import ProgressBar from 'primevue/progressbar';
import Ripple from 'primevue/ripple';
import StyleClass from 'primevue/styleclass';
import Toast from 'primevue/toast';
import ToastService from 'primevue/toastservice';
import Tooltip from 'primevue/tooltip';

import Menubar from "primevue/menubar";
import Select from "primevue/select";
import InputNumber from "primevue/inputnumber";
import DatePicker from 'primevue/datepicker';
import Chip from 'primevue/chip';
import AutoComplete from 'primevue/autocomplete';
import MultiSelect from 'primevue/multiselect';
import FileUpload from 'primevue/fileupload';
import Textarea from 'primevue/textarea';
import Accordion from 'primevue/accordion';
import AccordionTab from 'primevue/accordiontab';
import AccordionPanel from 'primevue/accordionpanel';
import AccordionHeader from 'primevue/accordionheader';
import AccordionContent from 'primevue/accordioncontent';

//-------------/PrimeVue Imports

//-------------CRUD PrimeVue Imports

import Badge from "primevue/badge";
import Button from "primevue/button";
import Panel from "primevue/panel";
import InputText from "primevue/inputtext";
import Column from "primevue/column";
import DataTable from "primevue/datatable";
import Paginator from "primevue/paginator";
import Divider from "primevue/divider";
import RadioButton from "primevue/radiobutton";
import Message from "primevue/message";
import Tag from "primevue/tag";

import Dialog from "primevue/dialog";
import Checkbox from "primevue/checkbox";
import ConfirmPopup from "primevue/confirmpopup";
import ToggleButton from "primevue/togglebutton";
import Chart from 'primevue/chart';

//-------------/CRUD PrimeVue Imports



//-------------APP
import App from './layouts/App.vue'
import router from './routes/router'

const app = createApp(App);

const pinia = createPinia();
pinia.use(({ store }) => {
    store.$router = markRaw(router)
});
app.use(pinia);
app.use(PiniaVuePlugin);
app.use(router);
//-------------/APP

const vaahstore = definePreset(Aura, {
    semantic: {
        primary: {
        }
    }
});
//-------------PrimeVue Use
app.use(PrimeVue, {
    theme: {
        preset: vaahstore,
        darkMode: false,
    }
});
app.use(ConfirmationService);
app.use(ToastService);
app.use(DialogService);

app.directive('tooltip', Tooltip);
app.directive('badge', BadgeDirective);
app.directive('ripple', Ripple);
app.directive('styleclass', StyleClass);


app.component('ConfirmDialog', ConfirmDialog);
app.component('Menu', Menu);
app.component('ProgressBar', ProgressBar);
app.component('Toast', Toast);
app.component('Chip', Chip);
//-------------/PrimeVue Use

// -------------CRUD PrimeVue Use

app.component('Badge', Badge);
app.component('Button', Button);
app.component('Panel', Panel);
app.component('RadioButton', RadioButton);
app.component('InputText', InputText);
app.component('Column', Column);
app.component('Paginator', Paginator);
app.component('Divider', Divider);
app.component('DataTable', DataTable);
app.component('Message', Message);
app.component('Tag', Tag);
app.component('Dialog', Dialog);
app.component('Checkbox', Checkbox);
app.component('ConfirmPopup', ConfirmPopup);
app.component('ToggleButton', ToggleButton);
app.component('Menubar', Menubar);
app.component('Select', Select);
app.component('InputNumber', InputNumber);
app.component('DatePicker', DatePicker);
app.component('AutoComplete', AutoComplete);
app.component('MultiSelect', MultiSelect);
app.component('FileUpload', FileUpload);
app.component('Textarea', Textarea);
app.component('Accordion', Accordion);
app.component('AccordionTab', AccordionTab);
app.component('AccordionHeader', AccordionHeader);
app.component('AccordionContent', AccordionContent);
app.component('AccordionPanel', AccordionPanel);
app.component('Chart', Chart);

//-------------/CRUD PrimeVue Use

import InputGroup from "primevue/inputgroup";
app.component("InputGroup", InputGroup);

import ToggleSwitch from "primevue/toggleswitch";
app.component("ToggleSwitch", ToggleSwitch);

import FloatLabel from "primevue/floatlabel";
app.component("FloatLabel", FloatLabel);


import Card from "primevue/card";
app.component("Card", Card);

import Image from "primevue/Image";
app.component("Image", Image);


app.component('apexchart', VueApexCharts);

app.mount('#appStore')


export { app }

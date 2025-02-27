import { createApp, markRaw } from 'vue';
import { createPinia, PiniaVuePlugin } from 'pinia'
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

import Chip from 'primevue/chip';
import SelectButton from 'primevue/selectbutton';

import Chart from 'primevue/chart';
import Stepper from 'primevue/stepper';
import FileUpload from 'primevue/fileupload';

import Accordion from 'primevue/accordion';
import AccordionTab from 'primevue/accordiontab';
import AccordionPanel from 'primevue/accordionpanel';
import AccordionHeader from 'primevue/accordionheader';
import AccordionContent from 'primevue/accordioncontent';

import Tabs from 'primevue/tabs';
import TabList from 'primevue/tablist';
import Tab from 'primevue/tab';
import TabPanels from 'primevue/tabpanels';
import TabPanel from 'primevue/tabpanel';
import InputGroupAddon from 'primevue/inputgroupaddon';

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
    semantic: {}
});
//-------------PrimeVue Use
app.use(PrimeVue, {
    theme: {
        preset: vaahstore,
        darkMode: false,
        options: {
            darkModeSelector: false,
            darkModeInject: false
        },
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
app.component('SelectButton', SelectButton);
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
app.component('InputGroupAddon', InputGroupAddon);


app.component('FileUpload', FileUpload);

app.component('Accordion', Accordion);
app.component('AccordionTab', AccordionTab);
app.component('AccordionHeader', AccordionHeader);
app.component('AccordionContent', AccordionContent);
app.component('AccordionPanel', AccordionPanel);
app.component('Chart', Chart);
app.component('Stepper', Stepper);
app.component('Tabs', Tabs);
app.component('Tab', Tab);
app.component('TabPanels', TabPanels);
app.component('TabPanel', TabPanel);
app.component('TabList', TabList);

//-------------/CRUD PrimeVue Use

import TreeSelect from 'primevue/treeselect';
app.component('TreeSelect', TreeSelect);


import MultiSelect from 'primevue/multiselect';
app.component('MultiSelect', MultiSelect);


import AutoComplete from 'primevue/autocomplete';
app.component('AutoComplete', AutoComplete);


import InputGroup from "primevue/inputgroup";
app.component("InputGroup", InputGroup);

import ToggleSwitch from "primevue/toggleswitch";
app.component("ToggleSwitch", ToggleSwitch);

import FloatLabel from "primevue/floatlabel";
app.component("FloatLabel", FloatLabel);


import Card from "primevue/card";
app.component("Card", Card);

import Image from "primevue/Image";
import { semantic } from '@primeuix/themes/aura/base';
import { Warning } from 'postcss';
app.component("Image", Image);


app.component('apexchart', VueApexCharts);




import DatePicker from 'primevue/datepicker';
app.component('DatePicker', DatePicker);

import Textarea from 'primevue/textarea';
app.component('Textarea', Textarea);

import Chips from 'primevue/chips';
app.component('Chips', Chips);


import Editor from 'primevue/editor';
app.component('Editor', Editor);


import Drawer from 'primevue/drawer';
app.component('Drawer', Drawer);


import DynamicDialog from 'primevue/dynamicdialog';
app.component('DynamicDialog', DynamicDialog);




app.mount('#appStore')


export { app }

import ResponsiveFieldtype from "./ResponsiveFieldtype.vue";
import ResponsiveFieldtypeIndex from "./ResponsiveFieldtypeIndex.vue";

Statamic.booting(() => {
    Statamic.$components.register('responsive-fieldtype', ResponsiveFieldtype);
    Statamic.$components.register('responsive-fieldtype-index', ResponsiveFieldtypeIndex);
})

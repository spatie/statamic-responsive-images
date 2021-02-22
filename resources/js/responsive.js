import ResponsiveFieldtype from "./ResponsiveFieldtype";
import ResponsiveFieldtypeIndex from "./ResponsiveFieldtypeIndex";

Statamic.booting(() => {
    Statamic.$components.register('responsive-fieldtype', ResponsiveFieldtype);
    Statamic.$components.register('responsive-fieldtype-index', ResponsiveFieldtypeIndex);
})

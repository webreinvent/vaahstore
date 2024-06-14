let routes= [];

// import dashboard from "./vue-routes-dashboard";
import brand from "./vue-routes-brands";
import store from "./vue-routes-stores";
import vendor from "./vue-routes-vendors";
import product from "./vue-routes-products";
import warehouse from "./vue-routes-warehouses";
import productvendors from "./vue-routes-productvendors";
import productvariation from "./vue-routes-productvariations";
import productmedias from "./vue-routes-productmedias";
import productstocks from "./vue-routes-productstocks";
import attributes from "./vue-routes-attributes";
import attributeGroups from "./vue-routes-attributegroups";
import orders from "./vue-routes-orders";
import paymentmethods from "./vue-routes-paymentmethods";
import storepaymentmethods from "./vue-routes-storepaymentmethods";
import addresses from "./vue-routes-addresses";
import customergroups from "./vue-routes-customergroups";
import wishlists from "./vue-routes-wishlists";
import productAttribute from "./vue-routes-productattributes";
import categories from "./vue-routes-categories";
import users from "./vue-routes-users";
import cart from "./vue-routes-carts";
import settings from "./vue-routes-settings";

// routes = routes.concat(dashboard);
routes = routes.concat(brand);
routes = routes.concat(store);
routes = routes.concat(vendor);
routes = routes.concat(product);
routes = routes.concat(warehouse);
routes = routes.concat(productvendors);
routes = routes.concat(productvariation);
routes = routes.concat(productmedias);
routes = routes.concat(orders);
routes = routes.concat(productstocks);
routes = routes.concat(attributes);
routes = routes.concat(paymentmethods);
routes = routes.concat(storepaymentmethods);
routes = routes.concat(addresses);
routes = routes.concat(customergroups);
routes = routes.concat(wishlists);
routes = routes.concat(attributeGroups);
routes = routes.concat(productAttribute);
routes = routes.concat(users);
routes = routes.concat(categories);
routes = routes.concat(cart);
routes = routes.concat(settings);



export default routes;

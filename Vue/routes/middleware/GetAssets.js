export default async function GetAssets ({ to, from, next, store }){
    await store.dispatch('root/getAssets');
    return next()
}

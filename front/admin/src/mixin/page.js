export const page = {
    data() {
        return {
            tabs: [],
        }
    },
    created() {
        if ((this.$route.hash === null || this.$route.hash === '') && this.tabs.length > 0) {
            this.$router.replace({hash: this.tabs[0].hash});
        }
    },
    mounted() {
        let breadcrumbs = [];
        let currentRoute = this.$router.currentRoute;
        let matchedRoutes = currentRoute.matched;
        let matchedRoutesNum = matchedRoutes.length;
        let name = null;
        let to = null;
        for (let i in matchedRoutes) {
            if (to + '/' === matchedRoutes[i].path) {
                break;
            }
            name = matchedRoutes[i].meta.name;
            if (matchedRoutes[i].name === currentRoute.name) {
                for (let ii in currentRoute.params) {
                    name = name.replace('{'+ii+'}', currentRoute.params[ii]);
                }
            }
            to = matchedRoutes[i].path;
            let disabled = i - 0 === matchedRoutesNum - 1 || true === matchedRoutes[i].meta.isNotPage;
            let el = {
                text: name,
                to: to,
                disabled: disabled,
                exact: true,
            };
            breadcrumbs.push(el);
        }
        this.$store.commit('breadcrumbs/set', breadcrumbs);
    },
};

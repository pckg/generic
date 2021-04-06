<template>
    <div>
        <pckg-raw v-for="tabelize in $route.meta.resolved.tabelizes"
                  :body="tabelize"></pckg-raw>
        <pckg-raw v-for="functionize in $route.meta.resolved.functionizes"
                  :body="functionize"></pckg-raw>
    </div>
</template>

<script>
export default {}
</script>

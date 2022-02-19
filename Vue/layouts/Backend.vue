<script src="./BackendJs.js"></script>

<template>

    <!--module-->
    <div class="container is-fluid is-paddingless">

    <!--columns-->
    <div class="columns">
      <div class="column is-2">
        <AsideMenu/>
      </div>

      <div class="column is-10">

        <router-view></router-view>

      </div>
    </div>
    <!--/columns-->

      <Footer/>

  </div>
    <!--module-->


</template>


<div class="bootstrap lg_brand_wrap">
  <div class="row lg-brands-section">
    <div class="col-sm-12">
      <p>Please drug the following brands to "New Selected Brands" section to display them in home page </p>
    </div>
    <div class="col-sm-6">
      <h2>Available Brands</h2>
      <ul id="new_brands1" class="new_brands_sortable brands_sortable">
        {if isset($available_new_brands)}
          {foreach $available_new_brands as $brand}
            <li class="ui-state-default" id="{$brand['id']}">{$brand['name']}</li>
          {/foreach}
        {/if}
      </ul>
    </div>
    <div class="col-sm-6">
      <h2>New Selected Brands</h2>
      <ul id="new_brands2" class="new_brands_sortable brands_sortable">
        {if isset($new_brands) }
          {foreach $new_brands as $brand}
            <li class="ui-state-default" id="{$brand['id']}">{$brand['name']}</li>
          {/foreach}
        {/if}
      </ul>
    </div>
  </div>
  <div class="row lg-brands-section">
    <div class="col-sm-12">
      <p>Please drug the following brands to "Top Selected Brands" section to display them in home page </p>
    </div>
    <div class="col-sm-6">
      <h2>Available Brands</h2>
      <ul id="top_brands1" class="top_brands_sortable brands_sortable">
        {if isset($available_top_brands)}
          {foreach $available_top_brands as $brand}
            <li class="ui-state-default" id="{$brand['id']}">{$brand['name']}</li>
          {/foreach}
        {/if}
      </ul>
    </div>
    <div class="col-sm-6">
      <h2>Selected Top Brands</h2>
      <ul id="top_brands2" class="top_brands_sortable brands_sortable">
        {if isset($top_brands) }
          {foreach $top_brands as $brand}
            <li class="ui-state-default" id="{$brand['id']}">{$brand['name']}</li>
          {/foreach}
        {/if}
      </ul>
    </div>
  </div>
  
  <div class="row lg-brands-section">
    <div class="col-sm-12">
      <p>Please drug the following brands to "Featured Selected Brands" section to display them in home page </p>
    </div>
    <div class="col-sm-6">
      <h2>Available Brands</h2>
      <ul id="featured_brands1" class="featured_brands_sortable brands_sortable">
        {if isset($available_featured_brands)}
          {foreach $available_featured_brands as $brand}
            <li class="ui-state-default" id="{$brand['id']}">{$brand['name']}</li>
          {/foreach}
        {/if}
      </ul>
    </div>
    <div class="col-sm-6">
      <h2>Selected Featured Brands</h2>
      <ul id="featured_brands2" class="featured_brands_sortable brands_sortable">
        {if isset($featured_brands) }
          {foreach $featured_brands as $brand}
            <li class="ui-state-default" id="{$brand['id']}">{$brand['name']}</li>
          {/foreach}
        {/if}
      </ul>
    </div>
  </div>
  
  <div class="row lg-brands-section">
    <div class="col-sm-12">
      <p>Please drug the following brands to "Populer Selected Brands" section to display them in home page </p>
    </div>
    <div class="col-sm-6">
      <h2>Available Populer Brands</h2>
      <ul id="populer_brands1" class="populer_brands_sortable brands_sortable">
        {if isset($available_populer_brands)}
          {foreach $available_populer_brands as $brand}
            <li class="ui-state-default" id="{$brand['id']}">{$brand['name']}</li>
          {/foreach}
        {/if}
      </ul>
    </div>
    <div class="col-sm-6">
      <h2>populer Selected Brands</h2>
      <ul id="populer_brands2" class="populer_brands_sortable brands_sortable">
        {if isset($populer_brands) }
          {foreach $populer_brands as $brand}
            <li class="ui-state-default" id="{$brand['id']}">{$brand['name']}</li>
          {/foreach}
        {/if}
      </ul>
    </div>
  </div>
</div>
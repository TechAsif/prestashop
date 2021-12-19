<div class="bootstrap lg_brand_wrap">
  <div class="row lg-brands-section">
    <div class="col-sm-12">
      <p>Please drug the following brands to "Custom Selected Brands" section to display them in home page </p>
    </div>
    <div class="col-sm-6">
      <h2>Available Brands</h2>
      <ul id="custom_brands1" class="custom_brands_sortable brands_sortable">
        {if isset($available_custom_brands)}
          {foreach $available_custom_brands as $brand}
            <li class="ui-state-default" id="{$brand['id']}">{$brand['name']}</li>
          {/foreach}
        {/if}
      </ul>
    </div>
    <div class="col-sm-6">
      <h2>Custom Selected Brands</h2>
      <ul id="custom_brands2" class="custom_brands_sortable brands_sortable">
        {if isset($custom_brands) }
          {foreach $custom_brands as $brand}
            <li class="ui-state-default" id="{$brand['id']}">{$brand['name']}</li>
          {/foreach}
        {/if}
      </ul>
    </div>
  </div>
  <div class="row lg-brands-section">
    <div class="col-sm-12">
      <p>Please drug the following brands to "top Selected Brands" section to display them in home page </p>
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
      <h2>top Selected Brands</h2>
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
      <p>Please drug the following brands to "featured Selected Brands" section to display them in home page </p>
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
      <h2>featured Selected Brands</h2>
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
      <p>Please drug the following brands to "populer Selected Brands" section to display them in home page </p>
    </div>
    <div class="col-sm-6">
      <h2>Available Brands</h2>
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
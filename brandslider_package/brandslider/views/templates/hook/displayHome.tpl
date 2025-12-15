{*
 * Brand Slider Template
 * 
 * Override this file in your child theme:
 * themes/YOUR_CHILD_THEME/modules/brandslider/views/templates/hook/displayHome.tpl
 *}

{if $brandslider_categories|count > 0}
<section class="brand-slider-section">
    <div class="container">
        {if $brandslider_show_title && $brandslider_title}
            <h2 class="brand-slider-title">{$brandslider_title}</h2>
        {/if}
        
        <div class="brand-slider-wrapper">
            <div class="brand-slider" 
                 data-items-visible="{$brandslider_items_visible}"
                 data-speed="{$brandslider_speed}"
                 data-autoplay="{if $brandslider_autoplay}true{else}false{/if}"
                 data-autoplay-speed="{$brandslider_autoplay_speed}"
                 data-show-nav="{if $brandslider_show_nav}true{else}false{/if}"
                 data-show-dots="{if $brandslider_show_dots}true{else}false{/if}">
                
                <div class="brand-slider-track">
                    {foreach from=$brandslider_categories item=category}
                        <div class="brand-slide">
                            <a href="{$category.link}" title="{$category.name|escape:'html':'UTF-8'}">
                                <div class="brand-slide-inner">
                                    {if $category.image}
                                        <img src="{$category.image}" 
                                             alt="{$category.name|escape:'html':'UTF-8'}" 
                                             class="brand-image"
                                             loading="lazy" />
                                    {else}
                                        <div class="brand-placeholder">
                                            <span>{$category.name|truncate:2:'':true|upper}</span>
                                        </div>
                                    {/if}
                                    <span class="brand-name">{$category.name}</span>
                                </div>
                            </a>
                        </div>
                    {/foreach}
                </div>
            </div>
            
            {if $brandslider_show_nav}
                <button class="brand-slider-nav brand-slider-prev" aria-label="{l s='Previous' mod='brandslider'}">
                    <svg viewBox="0 0 24 24" width="24" height="24">
                        <path fill="currentColor" d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z"/>
                    </svg>
                </button>
                <button class="brand-slider-nav brand-slider-next" aria-label="{l s='Next' mod='brandslider'}">
                    <svg viewBox="0 0 24 24" width="24" height="24">
                        <path fill="currentColor" d="M10 6L8.59 7.41 13.17 12l-4.58 4.59L10 18l6-6z"/>
                    </svg>
                </button>
            {/if}
        </div>
        
        {if $brandslider_show_dots}
            <div class="brand-slider-dots"></div>
        {/if}
    </div>
</section>
{/if}

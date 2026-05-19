// app.js — mobile "app-like" + safe DataTables init + table->cards
$(function(){
  // Sidebar toggle
  const $sidebar = $('.sidebar');
  const $overlay = $('#sidebarOverlay');
  $('#navToggle').on('click', function(){
    $sidebar.toggleClass('open');
    $overlay.toggleClass('show', $sidebar.hasClass('open'));
  });
  $overlay.on('click', function(){ $sidebar.removeClass('open'); $overlay.removeClass('show'); });

  // Helper: transform a table to cards
  function tableToCards($table){
    if ($table.data('cards-made')) return;
    const headers = [];
    $table.find('thead th').each(function(){ headers.push($(this).text().trim()); });

    const $cards = $('<div class="mobile-cards"></div>');
    $table.find('tbody tr').each(function(){
      const $tds = $(this).children('td');
      if (!$tds.length) return;
      // choose a title (prefer Name, Subject, Customer; else first non-ID col)
      let title = '';
      for (let i=0;i<headers.length && !title;i++){
        const h = (headers[i]||'').toLowerCase();
        if (h==='id') continue;
        if (['name','subject','customer','from','email'].includes(h)) title = $tds.eq(i).text().trim();
      }
      if (!title) title = $tds.eq(0).text().trim();

      const $card = $('<div class="mcard"></div>');
      $card.append($('<div class="mcard-title"></div>').text(title));

      for (let i=0;i<headers.length;i++){
        const h = headers[i] || '';
        const v = $tds.eq(i).clone(); // keep links
        const hl = h.toLowerCase();
        if (hl==='id' || hl==='actions') continue;
        const $row = $('<div class="mrow"><div class="label"></div><div class="value"></div></div>');
        $row.find('.label').text(h);
        $row.find('.value').append(v.contents());
        $card.append($row);
      }

      // actions (last col) if present
      const actIdx = headers.findIndex(h => (h||'').toLowerCase()==='actions');
      if (actIdx >= 0){
        const $actWrap = $('<div class="mcard-actions"></div>');
        $actWrap.append($tds.eq(actIdx).contents());
        $card.append($actWrap);
      }

      $cards.append($card);
    });

    $cards.insertAfter($table);
    $table.data('cards-made', true);
  }

  function initUI(){
    const isMobile = window.matchMedia('(max-width: 900px)').matches;

    // Bottom tabbar active link
    $('.mobile-tabbar a').each(function(){
      const href = $(this).attr('href');
      if (location.pathname.endsWith(href)) $(this).addClass('active');
    });

    if (isMobile){
      // Do NOT init DataTables on mobile; instead convert to cards
      $('.datatable').each(function(){ tableToCards($(this)); });
    } else {
      // Desktop: DataTables, init once per table
      if ($.fn && $.fn.DataTable){
        $('.datatable').each(function(){
          if ($.fn.DataTable.isDataTable(this)) return;
          const $t = $(this);
          const userOpts = $t.data('dt-options') || {};
          const defaults = { pageLength: 25, responsive: true, scrollX: true, autoWidth: false };
          $t.DataTable($.extend(true, {}, defaults, userOpts));
        });
      }
    }
  }

  initUI();
  // Re-evaluate on resize (debounced)
  let t=null;
  $(window).on('resize', function(){ clearTimeout(t); t=setTimeout(initUI, 200); });
});

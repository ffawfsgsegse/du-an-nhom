/* 3DPrintHub Admin: cập nhật 4 bảng xếp hạng từ admin_api.php. */
(() => {
  const formatNumber = (value) => new Intl.NumberFormat('vi-VN').format(Number(value) || 0);
  const formatMoney = (value) => formatNumber(value) + 'đ';
  const emptyText = {
    top_products: 'Chưa có sản phẩm đang mở bán.',
    bottom_products: 'Chưa có sản phẩm đang mở bán.',
    top_locations: 'Chưa có tỉnh/thành xác định được.',
    bottom_locations: 'Chưa có tỉnh/thành xác định được.'
  };
  const make = (tag, className, text) => {
    const node = document.createElement(tag);
    if (className) node.className = className;
    if (text !== undefined) node.textContent = String(text);
    return node;
  };
  const update = (key, items, kind) => {
    document.querySelectorAll('[data-insight-list="' + key + '"]').forEach(list => {
      const fragment = document.createDocumentFragment();
      if (!items.length) {
        fragment.append(make('p', 'admin-insight-empty', emptyText[key]));
      } else {
        items.forEach((entry, index) => {
          const row = make('div', 'admin-insight-item');
          row.append(make('span', 'admin-insight-position', index + 1));
          const info = make('div', 'admin-insight-info');
          info.append(make('strong', '', entry.name || 'Không có tên'));
          info.append(make('small', '', kind === 'products' ? (entry.shop_name || '') : formatMoney(entry.revenue)));
          row.append(info);
          const value = make('span', 'admin-insight-value');
          value.append(make('b', '', formatNumber(kind === 'products' ? entry.units_sold : entry.orders_count)));
          value.append(make('small', '', kind === 'products' ? 'chiếc' : 'đơn'));
          row.append(value);
          fragment.append(row);
        });
      }
      list.replaceChildren(fragment);
    });
  };
  document.addEventListener('admin:data-updated', event => {
    const data = event.detail?.sales_insights;
    if (!data) return;
    if (Array.isArray(data.top_products)) update('top_products', data.top_products, 'products');
    if (Array.isArray(data.bottom_products)) update('bottom_products', data.bottom_products, 'products');
    if (Array.isArray(data.locations?.top)) update('top_locations', data.locations.top, 'locations');
    if (Array.isArray(data.locations?.bottom)) update('bottom_locations', data.locations.bottom, 'locations');
    document.querySelectorAll('[data-location-unknown]').forEach(note => {
      const count = Number(data.locations?.unknown_orders) || 0;
      note.textContent = count > 0 ? ' Có ' + formatNumber(count) + ' đơn hoàn tất chưa xác định được tỉnh/thành, không đưa vào bảng xếp hạng.' : '';
    });
  });
})();

import applySelect2 from '../lib/applySelect2';

ensureRemoverDisabled();

$(document).on('click', '.collection-item-adder', function () {
  const $prototype = $(this).closest('.form-collection').find('.prototype');
  const $placeholder = $(this).closest('.form-collection').find('.placeholder');

  let lastIndex = parseInt($prototype.data('last-index'));
  const html = $prototype.data('prototype').replace(/__name__/g, ++lastIndex);
  const $element = $(html);
  $placeholder.append($element);
  $prototype.data('lastIndex', lastIndex);

  applySelect2($element.find('select'));
  ensureRemoverDisabled();
});

$(document).on('click', '.collection-item-remover', function () {
  $(this).closest('.collection-item').remove();

  ensureRemoverDisabled();
});

function ensureRemoverDisabled() {
  $('.prototype').each(function () {
    const requiredNum = $(this).data('required-num');
    const $removers = $(this).closest('.form-collection').find('.collection-item-remover');

    if ($removers.length <= requiredNum) {
      $removers.prop('disabled', true);
    } else {
      $removers.prop('disabled', false);
    }
  });
}

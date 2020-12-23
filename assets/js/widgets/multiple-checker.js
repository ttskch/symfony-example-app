const $checker = $('.multiple-checker');
const $allChecker = $('.multiple-checker-all');
let $lastTouchedChecker = null;

$allChecker.on('click', function () {
  $checker.prop('checked', !allChecked());
  updateView();
});

$checker.on('click', function (e) {
  if (e.shiftKey) {
    // determine target range
    let indexFrom = $checker.index(0);
    let indexTo = $checker.index($(this)) - 1;
    if ($lastTouchedChecker) {
      indexFrom = Math.min($checker.index($(this)), $checker.index($lastTouchedChecker)) +1;
      indexTo = Math.max($checker.index($(this)), $checker.index($lastTouchedChecker)) - 1;
    }

    // turn off all checkboxes if all in target range are on, otherwise turn on all
    let allChecked = true;
    for (let i = indexFrom; i <= indexTo; i++) {
      if (!$checker.eq(i).prop('checked')) {
        allChecked = false;
        break;
      }
    }
    for (let i = indexFrom; i <= indexTo; i++) {
      $checker.eq(i).prop('checked', !allChecked);
    }
  }

  $allChecker.prop('checked', allChecked());
  updateView();

  $lastTouchedChecker = $(this);
});

function allChecked() {
  let result = true;

  $checker.each(function () {
    if (!$(this).prop('checked')) {
      result = false;
      return false;
    }
  });

  return result;
}

function updateView() {
  const $checked = $('.multiple-checker:checked');
  const $unchecked = $('.multiple-checker:not(:checked)');

  let ids = [];

  $checked.each(function () {
    ids.push($(this).val());
  });

  $('input[name="ids"]').val(ids.join(','));

  if (ids.length > 0) {
    $('.multiple-checker-action').removeClass('disabled');
  } else {
    $('.multiple-checker-action').addClass('disabled');
  }

  $checked.closest('tr').addClass('table-active');
  $unchecked.closest('tr').removeClass('table-active');
}

// init view even when user came from browser history
setTimeout(function () {
  updateView();
}, 0);

// enable to check by clicking row
$tr = $('table tbody tr');
$tr.on('click', function (e) {
  // if some text range is selected, ignore clicking
  if (document.getSelection().type === 'Range') {
    return false;
  }

  $(this).find('.multiple-checker').trigger(e);
});
$tr.find('input, a').on('click', function (e) {
  e.stopPropagation();
});

// disable selecting text range when Shift-key is down
$(document).on('keydown keyup', function (e) {
  if (e.key === 'Shift') {
    document.onselectstart = function () {
      return e.type !== 'keydown';
    };
  }
});

updateView();

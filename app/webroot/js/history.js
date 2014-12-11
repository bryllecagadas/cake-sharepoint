(function($) {
  History = {
    base: null,
    init: function() {
      History.base = $('base').attr('href');

      $(document).on('click', '.history-link', function() {
        var $this = $(this);
        $.ajax({
          url: History.base + 'ajax/projects/file_history',
          dataType: 'json',
          type: 'POST',
          data: {
            project_id: Files.secureId,
            node_id: $this.data('id')
          },
          success: function(response) {

            var modal_body = $('#historyModal').find('.modal-body').empty();

            $('#historyModal').modal().find('.modal-title')
              .html('History for ' + response.name);

            if (response.logs.length) {
              modal_body.append(
                History.createList(response.logs)
              );
            }
          }
        });
        return false;
      });
    },
    createList: function(logs) {
      var $ul = $('<ul>');
      for (var i in logs) {
        var $li = $('<li>');
        $ul.append(
          $li.html(logs[i].message)
        );
      }

      return $ul;
    }
  };

  $(document).ready(function() {
    History.init();
  })
})(jQuery);
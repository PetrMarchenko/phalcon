<!DOCTYPE html>
<html lang="en">
  <head>
    {% include "../../../views/templates/head.volt" %}

    <link href="/fuelux/css/fuelux.css" rel="stylesheet">
    <link href="/fuelux/css/fuelux-responsive.css" rel="stylesheet">
    <link rel="stylesheet" href="/css/jquery-ui.css" />
    <link href="/css/custom.css" rel="stylesheet">

  </head>

  <body class="fuelux" data-path="{{ data_path() }}">

    {% include "../../../views/templates/menu.volt" %}

    <div id="messages">
      {% if flash.has() %}
        {% for type, messages in flash.getMessages() %}
            {% for message in messages %}
                <div class="alert alert-{{ type }}">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    {{ message }}
                </div>
            {% endfor%}
        {% endfor %}
      {% endif%}
    </div>

    <div class="container">
      <?php echo $this->getContent(); ?>
    </div><!-- /.container -->

  </body>
</html>
<div class="navbar navbar-inverse navbar-fixed-top" role="navigation">
  <div class="container">
    <div class="navbar-header">
      <a class="navbar-brand" href="{{ url(["for": "home"]) }}">Shark starter</a>
    </div>
    <div class="collapse navbar-collapse">
      <ul class="nav navbar-nav">
        <li class="{{isActive("home/index/index")}}" ><a href="{{ url(["for": "home"]) }}">Home</a></li>
        <li class="{{isActive("home/index/about")}}" ><a href="{{ url(["for": "about"]) }}">About</a></li>
        <li class="{{isActive("home/index/contact")}}" ><a href="{{ url(["for": "contact"]) }}">Contact</a></li>
        {% if (sharkUserRoleId == ROLE_ADMIN) %}
            <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="true">Management <span class="caret"></span></a>
                <ul class="dropdown-menu">
                  <li><a href="{{ url(["for": "user_show"]) }}">User</a></li>
                  <li><a href="{{ url(["for": "rbac_show"]) }}">Rbac</a></li>
                  <li><a href="{{ url(["for": "mail_template_show"]) }}">Mail Template</a></li>
                </ul>
            </li>
        {% endif%}
      </ul>

      <ul class="nav navbar-nav navbar-right">
        {% if (sharkUserRoleId === ROLE_GUESTS) %}
            <li><a href="{{ url(["for": "login"]) }}">Login</a></li>
            <li><a href="{{ url(["for": "login_registration"]) }}">Registration</a></li>
        {% else %}
           <li><a href="{{ url(["for": "logout"]) }}">logout</a></li>
        {% endif%}
      </ul>

    </div><!--/.nav-collapse -->
  </div>
</div>
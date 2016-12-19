    <br />
    <br />
    <div class="container">
        {{ form(["for": "login_auth"], "method":"post", "class":"form-horizontal") }}
            <?php foreach ($form as $element) {?>
                <div class="form-group">
                    <label class="col-md-4 control-label" for="name"> <?php echo $element->getLabel() ?></label>
                    <div class="col-md-4">
                    <?php echo $element->render()?>
                    <?php
                        $messages = $form->getMessagesFor(
                            $element->getName()
                        );
                    if (count($messages)) {
                        echo '<div class="messages">';
                        foreach ($messages as $message) {
                            echo $message;
                        }
                        echo "</div>";
                    }
                    ?>
                    </div>
                </div>
            <?php } ?>

            <div class="form-group">
              <label class="col-md-4 control-label" for="name"></label>
              <div class="col-md-4">
              {{ link_to(["for": "login_forgot"], "Forgot password") }}
              </div>
            </div>

            <div class="form-group">
              <label class="col-md-4 control-label" for="name"></label>
              <div class="col-md-4">
              {{ link_to(["for": "home"], "Cancel", "class": "btn btn-primary") }}
              {{ submit_button("Login","class": "btn btn-success") }}
              </div>
            </div>
        {{ endForm() }}
    </div>
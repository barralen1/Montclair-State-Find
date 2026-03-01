import flet as ft

def main(page: ft.Page) -> None:
    page.title = "Auth Demo - Flet Template"
    page.vertical_alignment = ft.MainAxisAlignment.CENTER
    page.window_width = 360
    page.window_height = 640
    page.padding = 20
    page.theme_mode = ft.ThemeMode.LIGHT

    # In-memory user store
    users = {}

    # Core state
    current_user = {"email": "", "logged_in": False}

    # Error containers (start hidden)
    signin_email_error = ft.Container(content=ft.Text("", color=ft.Colors.RED, size=12), visible=False, padding=ft.padding.only(bottom=5))
    signin_password_error = ft.Container(content=ft.Text("", color=ft.Colors.RED, size=12), visible=False, padding=ft.padding.only(bottom=5))

    signup_email_error = ft.Container(content=ft.Text("", color=ft.Colors.RED, size=12), visible=False, padding=ft.padding.only(bottom=5))
    signup_password_error = ft.Container(content=ft.Text("", color=ft.Colors.RED, size=12), visible=False, padding=ft.padding.only(bottom=5))
    signup_confirm_error = ft.Container(content=ft.Text("", color=ft.Colors.RED, size=12), visible=False, padding=ft.padding.only(bottom=5))

    # UI controls
    signin_email = ft.TextField(label="Email", width=260)
    signin_password = ft.TextField(label="Password", width=260, password=True)
    signin_button = ft.ElevatedButton("Sign In", width=260, disabled=True)

    signup_email = ft.TextField(label="Email", width=260)
    signup_password = ft.TextField(label="Password", width=260, password=True)
    signup_confirm = ft.TextField(label="Confirm Password", width=260, password=True)
    signup_checkbox = ft.Checkbox(label="I agree to the terms", value=False)
    signup_button = ft.ElevatedButton("Sign Up", width=260, disabled=True)

    # Helpers for error handling
    def set_error(container: ft.Container, message: str) -> None:
        container.content.value = message
        container.visible = True

    def clear_error(container: ft.Container) -> None:
        container.content.value = ""
        container.visible = False

    # Validation helpers
    def is_valid_email(email: str) -> bool:
        import re
        pattern = r'^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$'
        return re.match(pattern, email) is not None

    def is_password_strong(password: str):
        if len(password) < 8:
            return False, "Password must be at least 8 characters"
        if not any(c.isupper() for c in password):
            return False, "Password must contain at least one uppercase letter"
        if not any(c.islower() for c in password):
            return False, "Password must contain at least one lowercase letter"
        if not any(c.isdigit() for c in password):
            return False, "Password must contain at least one number"
        return True, "Password is strong"

    # Screen builders
    def show_signin_screen():
        page.clean()
        page.appbar = None
        page.add(
            ft.Row(
                controls=[
                    ft.Column(
                        controls=[
                            ft.Text("Welcome Back!", size=24, weight=ft.FontWeight.BOLD),
                            signin_email,
                            signin_password,
                            signin_button,
                            signin_email_error,
                            signin_password_error,
                            ft.Row(
                                controls=[ft.Text("Don't have an account?"), ft.TextButton("Sign Up", on_click=lambda _: show_signup_screen())],
                                alignment=ft.MainAxisAlignment.CENTER,
                                spacing=5
                            )
                        ],
                        horizontal_alignment=ft.CrossAxisAlignment.CENTER,
                        spacing=15
                    )
                ],
                alignment=ft.MainAxisAlignment.CENTER
            )
        )
        # Clear fields and errors
        signin_email.value = ""
        signin_password.value = ""
        clear_error(signin_email_error)
        clear_error(signin_password_error)
        signin_button.disabled = True
        page.update()

    def show_signup_screen():
        page.clean()
        page.appbar = None
        page.add(
            ft.Row(
                controls=[
                    ft.Column(
                        controls=[
                            ft.Text("Create Account", size=24, weight=ft.FontWeight.BOLD),
                            signup_email,
                            signup_password,
                            signup_confirm,
                            signup_checkbox,
                            signup_button,
                            signup_email_error,
                            signup_password_error,
                            signup_confirm_error,
                            ft.Row(
                                controls=[ft.Text("Already have an account?"), ft.TextButton("Sign In", on_click=lambda _: show_signin_screen())],
                                alignment=ft.MainAxisAlignment.CENTER,
                                spacing=5
                            )
                        ],
                        horizontal_alignment=ft.CrossAxisAlignment.CENTER,
                        spacing=15
                    )
                ],
                alignment=ft.MainAxisAlignment.CENTER
            )
        )
        # Reset signup fields
        signup_email.value = ""
        signup_password.value = ""
        signup_confirm.value = ""
        signup_checkbox.value = False
        clear_error(signup_email_error)
        clear_error(signup_password_error)
        clear_error(signup_confirm_error)
        signup_button.disabled = True
        page.update()

    def open_lost_items_module():
    # Shared store for posts across the session
        if not hasattr(page, "lost_items_store"):
            page.lost_items_store = []

        # Clear current content and load Lost Items UI
        page.clean()

        # Import locally to avoid circular import
        from post import main as lost_main
        lost_main(page, on_back=show_welcome_page, posts_store=page.lost_items_store)    

    def show_welcome_page():
        page.clean()
        page.appbar = ft.AppBar(
            title=ft.Text(f"You're in!"),
            center_title=False,
            bgcolor=ft.Colors.RED_200,
            actions=[ft.IconButton(ft.Icons.EXIT_TO_APP, on_click=sign_out)]
        )
        page.add(
        ft.Column(
            controls=[
                ft.Row(controls=[ft.Text(f"Welcome!", size=20)], alignment=ft.MainAxisAlignment.CENTER),
                ft.Row(controls=[ft.Text("You're successfully signed in!", size=16)], alignment=ft.MainAxisAlignment.CENTER),
                # New: Lost Items button placed under the welcome text
                ft.Row(
                    controls=[
                        ft.Text(" ")  # optional spacer
                    ],
                    height=8  # optional vertical spacing
                ),
                ft.Row(
                    controls=[
                        ft.TextButton("Lost Items", on_click=lambda _: open_lost_items_module())
                    ],
                    alignment=ft.MainAxisAlignment.CENTER
                )
            ],
            alignment=ft.MainAxisAlignment.CENTER,
            spacing=20
        )
    )
    page.update()


    # Event handlers
    def on_validate_signin(_):
        email = signin_email.value
        pw = signin_password.value
        email_valid = is_valid_email(email) if email else False

        if email and not email_valid:
            set_error(signin_email_error, "Please enter a valid email address")
        else:
            clear_error(signin_email_error)

        # simple enable logic: both fields non-empty and email valid
        signin_button.disabled = not (email_valid and pw)
        page.update()

    def on_signin(_):
        email = signin_email.value
        pw = signin_password.value
        if not is_valid_email(email):
            set_error(signin_email_error, "Please enter a valid email address")
            page.update()
            return
        if email in users and users[email] == pw:
            current_user["email"] = email
            current_user["logged_in"] = True
            show_welcome_page()
        else:
            ft.dialog = ft.AlertDialog(title=ft.Text("Error"), content=ft.Text("Invalid email or password!"), actions=[ft.TextButton("OK", on_click=lambda e: setattr(page, "dialog_open", False))])
            show_error_dialog("Invalid email or password!")

    def on_signup_validate(_):
        email_valid = is_valid_email(signup_email.value) if signup_email.value else False
        if signup_email.value and not email_valid:
            set_error(signup_email_error, "Please enter a valid email address")
        else:
            clear_error(signup_email_error)

        pw_ok, pw_msg = (False, "")
        if signup_password.value:
            pw_ok, pw_msg = is_password_strong(signup_password.value)
            if not pw_ok:
                set_error(signup_password_error, pw_msg)
            else:
                clear_error(signup_password_error)
        else:
            clear_error(signup_password_error)

        passwords_match = signup_password.value == signup_confirm.value
        if signup_confirm.value and not passwords_match:
            set_error(signup_confirm_error, "Passwords do not match")
        else:
            clear_error(signup_confirm_error)

        signup_button.disabled = not (email_valid and pw_ok and passwords_match and signup_checkbox.value and signup_email.value)
        page.update()

    def on_signup(_):
        email = signup_email.value
        pw = signup_password.value
        if not is_valid_email(email):
            set_error(signup_email_error, "Please enter a valid email address")
            page.update()
            return
        pw_ok, _ = is_password_strong(pw)
        if not pw_ok:
            set_error(signup_password_error, "Password is not strong enough")
            page.update()
            return
        if pw != signup_confirm.value:
            set_error(signup_confirm_error, "Passwords do not match")
            page.update()
            return
        if email in users:
            show_error_dialog("User already exists!")
            return
        users[email] = pw
        current_user["email"] = email
        current_user["logged_in"] = True
        show_welcome_page()

    def sign_out(_):
        current_user["logged_in"] = False
        current_user["email"] = ""
        show_signin_screen()

    # Simple error dialog
    error_dialog_open = {"open": False}
    def show_error_dialog(message: str):
        d = ft.AlertDialog(
            title=ft.Text("Error"),
            content=ft.Text(message),
            actions=[ft.TextButton("OK", on_click=lambda e: setattr(page, "dialog_open", False))]
        )
        page.dialog = d
        d.open = True
        page.update()

    # Wire events
    signin_email.on_change = on_validate_signin
    signin_password.on_change = on_validate_signin
    signin_button.on_click = on_signin

    signup_email.on_change = on_signup_validate
    signup_password.on_change = on_signup_validate
    signup_confirm.on_change = on_signup_validate
    signup_checkbox.on_change = on_signup_validate
    signup_button.on_click = on_signup

    # Start
    show_signin_screen()

ft.app(target=main)



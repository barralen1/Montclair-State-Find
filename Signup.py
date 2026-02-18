import flet as ft
from flet import TextField, Checkbox, ElevatedButton, Text, Row, Column
from flet import ControlEvent



def main(page: ft.Page) -> None:
    page.title = 'Signup'
    page.vertical_alignment = ft.MainAxisAlignment.CENTER
    page.theme_mode = ft.ThemeMode.LIGHT
    page.window_width = 400
    page.window_height = 400
    page.window_resizable = False

    #Setup fields
    text_email: TextField = TextField(label='Email', text_align=ft.TextAlign.LEFT, width=200)
    text_password: TextField = TextField(label='Password', text_align=ft.TextAlign.LEFT, width=200, password=True)
    checkbox_signup: Checkbox = Checkbox(label='I agree to the conditions', value=False)
    button_submit: ElevatedButton = ElevatedButton(content=Text('Sign up'), width=200, disabled = True)

    def validate(e: ControlEvent) -> None:
        if all([text_email.value, text_password.value, checkbox_signup.value]):
            button_submit.disabled = False
        else:
            button_submit.disabled = True

        page.update()
    
    def submit(e: ControlEvent) -> None:
        print('Username:', text_email.value)
        print('Password:', text_password.value)

        page.clean()
        page.add(
            Row(
                controls=[Text(value="You're in", size=20)],
                alignment=ft.MainAxisAlignment.CENTER
            )
        )
    
    checkbox_signup.on_change = validate
    text_email.on_change = validate
    text_password.on_change = validate
    button_submit.on_click = submit

    #Render the page sign-up pag
    page.add(
        Row(
            controls=[
                Column(
                    [text_email,
                    text_password,
                    checkbox_signup,
                    button_submit]
                )
            ],
            alignment=ft.MainAxisAlignment.CENTER
        )
    )
    

if __name__ == '__main__':
    ft.app(target=main)    

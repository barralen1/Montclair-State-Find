
import flet as ft
from datetime import datetime

def main(page: ft.Page, on_back=None, posts_store=None, current_user=None) -> None:
    if posts_store is None:
        posts_store = []
    if current_user is None:
        current_user = {"email": "", "logged_in": False}
    
    page.title = "Lost Item Postings"
    page.window_width = 900
    page.window_height = 700
    page.padding = 20
    page.theme_mode = ft.ThemeMode.LIGHT

   
    if not hasattr(page, "lost_view"):
        page.lost_view = "list" 

    
    title_field = ft.TextField(label="Item Title", width=520)
    description_field = ft.TextField(label="Description", multiline=True, width=520, height=120)
    category_field = ft.TextField(label="Category (e.g., Wallet, Keys)", width=520)
    location_field = ft.TextField(label="Last Seen Location", width=520)
    date_field = ft.TextField(label="Date Lost (YYYY-MM-DD)", width=520)
    contact_field = ft.TextField(label="Contact Info", width=520)
    image_url_field = ft.TextField(label="Image URL (optional)", width=520)
    post_button = ft.ElevatedButton(
        "Post Lost Item", 
        width=200, 
        disabled=True,
        style=ft.ButtonStyle(
            bgcolor=ft.Colors.RED_400,
            color=ft.Colors.WHITE
        )
    )
    back_to_list_button = ft.ElevatedButton(
        "Back to List", 
        width=200,
        style=ft.ButtonStyle(
            bgcolor=ft.Colors.GREY_300,
            color=ft.Colors.BLACK
        )
    )
    status_dropdown = ft.Dropdown(
        label="Status",
        width=520,
        value="Lost",  # default value
        options=[
            ft.dropdown.Option("Lost"),
            ft.dropdown.Option("Found"),
            ft.dropdown.Option("Claimed"),
        ]
    )

   
    list_header = ft.Text("Lost Items List", size=22, weight=ft.FontWeight.BOLD)
    create_header = ft.Text("Create Lost Item", size=22, weight=ft.FontWeight.BOLD)

   
    def update_post_button(_=None):
        post_button.disabled = not (title_field.value and description_field.value and location_field.value and status_dropdown.value)
        page.update()
    status_dropdown.on_change = update_post_button    

    title_field.on_change = update_post_button
    description_field.on_change = update_post_button
    location_field.on_change = update_post_button

    page.appbar = ft.AppBar(
        title=ft.Text("Lost Items"),
        bgcolor=ft.Colors.RED_200,
        leading=ft.IconButton(ft.Icons.ARROW_BACK, on_click=lambda _: on_back()) if on_back else None
    )

    posts_list = ft.Column(spacing=8, width=700)

    
    def status_badge(status: str) -> ft.Container:
        color_map = {
            "Lost":    ft.Colors.RED_400,
            "Found":   ft.Colors.GREEN_400,
            "Claimed": ft.Colors.BLUE_400,
        }
        return ft.Container(
            content=ft.Text(
                status,
                color=ft.Colors.WHITE,
                size=12,
                weight=ft.FontWeight.BOLD
            ),
            bgcolor=color_map.get(status, ft.Colors.GREY_400),
            border_radius=12,
            padding=ft.padding.symmetric(horizontal=10, vertical=4)
        )

    
    def update_status(index: int, new_status: str):
        if 0 <= index < len(posts_store):
            posts_store[index]["status"] = new_status
            render_posts()

    def make_status_dropdown(index: int, current_status: str) -> ft.Dropdown:
        dd = ft.Dropdown(
            value=current_status,
            width=160,
            options=[
                ft.dropdown.Option("Lost"),
                ft.dropdown.Option("Found"),
                ft.dropdown.Option("Claimed"),
            ]
        )
        def on_status_change(e, idx=index):
            update_status(idx, dd.value)
        dd.on_change = on_status_change
        return dd

    def render_posts(filtered=None):
        display = filtered if filtered is not None else posts_store
        posts_list.controls.clear()

        if not display:
            posts_list.controls.append(ft.Text("No posts yet. Be the first to post."))
        else:
            for i, p in enumerate(display):
                status_dd = make_status_dropdown(i, p.get("status", "Lost"))
                is_author = p.get("author") == current_user.get("email")

                delete_button = ft.IconButton(
                    icon=ft.Icons.DELETE_OUTLINE,
                    icon_color=ft.Colors.RED_400,
                    tooltip="Delete Post",
                    on_click=lambda _, idx=i: delete_post(idx)
                ) if is_author else ft.Container()
                card = ft.Card(
                    content=ft.Container(
                        content=ft.Column(
                            controls=[
                                ft.Row(
                                    controls=[
                                        ft.Text(p["title"], size=16, weight=ft.FontWeight.BOLD, expand=True),
                                        status_badge(p.get("status", "Lost")), delete_button
                                    ],
                                    alignment=ft.MainAxisAlignment.SPACE_BETWEEN
                                ),
                                ft.Text(p["description"], size=13),
                                ft.Row(controls=[
                                    ft.Text(
                                        f"Category: {p['category']}  |  "
                                        f"Location: {p['location']}  |  "
                                        f"Date: {p['date']}"
                                    )
                                ]),
                                ft.Row(controls=[
                                    ft.Text(f"Contact: {p['contact']}")
                                ]),
                                ft.Row(
                                    controls=[
                                        status_dd,
                                        ft.Text("← Update Status", size=12, color=ft.Colors.GREY_600)
                                    ],
                                    spacing=10
                                )
                            ],
                            spacing=6
                        ),
                        padding=12
                    ),
                    elevation=2
                )
                posts_list.controls.append(card)
        page.update()



    def go_to_create(_):
        page.lost_view = "create"
        render_view()

    def post_item(_e):
        if not (title_field.value and description_field.value and location_field.value):
            return
        post = {
            "title": title_field.value,
            "description": description_field.value,
            "category": category_field.value or "Unspecified",
            "location": location_field.value,
            "date": date_field.value or datetime.now().strftime("%Y-%m-%d"),
            "contact": contact_field.value or "",
            "image": image_url_field.value or "",
            "status": status_dropdown.value,
            "author": current_user["email"]

        }
        posts_store.append(post)
        render_posts()
        #clear form
        title_field.value = ""
        description_field.value = ""
        category_field.value = ""
        location_field.value = ""
        date_field.value = ""
        contact_field.value = ""
        image_url_field.value = ""
        status_dropdown.value = "Lost"
        update_post_button()
        page.lost_view = "list"
        render_view()

    def delete_post(index: int):
        def confirm_delete(_):
            if 0 <= index < len(posts_store):
                posts_store.pop(index)
            dialog.open = False
            page.update()
            render_posts()

        def cancel_delete(_):
            dialog.open = False
            page.update()

        dialog = ft.AlertDialog(
            modal=True,
            title=ft.Text("Delete Post"),
            content=ft.Text("Are you sure you want to delete this post?"),
            actions=[
                ft.TextButton("Cancel", on_click=cancel_delete),
                ft.TextButton(
                    "Delete",
                    on_click=confirm_delete,
                    style=ft.ButtonStyle(color=ft.Colors.RED_400)
                )
            ],
            actions_alignment=ft.MainAxisAlignment.END
        )
        page.overlay.append(dialog)
        dialog.open = True
        page.update()


    def go_to_list(_e):
        page.lost_view = "list"
        render_view()

    back_to_list_button.on_click = go_to_list

    post_button.on_click = post_item

    def layout_list():
        return ft.Column(
            controls=[
                list_header,
                ft.Row(controls=[ft.ElevatedButton("Create Post", on_click=go_to_create, width=260)], alignment=ft.MainAxisAlignment.CENTER),
                ft.Text(""),  # spacer
                posts_list
            ],
            width=860,
            horizontal_alignment=ft.CrossAxisAlignment.CENTER
        )


    #create view layout
    def layout_create():
        return ft.Column(
            controls=[
                # Header
                ft.Row(
                    controls=[create_header],
                    alignment=ft.MainAxisAlignment.CENTER
                ),

                ft.Divider(),

                # Required fields section
                ft.Text("Required Information", size=14, 
                        weight=ft.FontWeight.BOLD, 
                        color=ft.Colors.RED_400),
                ft.Row(controls=[title_field], 
                    alignment=ft.MainAxisAlignment.CENTER),
                ft.Row(controls=[description_field], 
                    alignment=ft.MainAxisAlignment.CENTER),
                ft.Row(controls=[location_field], 
                    alignment=ft.MainAxisAlignment.CENTER),

                ft.Divider(),

                # Optional fields section
                ft.Text("Additional Information", size=14, 
                        weight=ft.FontWeight.BOLD, 
                        color=ft.Colors.GREY_600),
                ft.Row(controls=[category_field], 
                    alignment=ft.MainAxisAlignment.CENTER),
                ft.Row(controls=[date_field], 
                    alignment=ft.MainAxisAlignment.CENTER),
                ft.Row(controls=[contact_field], 
                    alignment=ft.MainAxisAlignment.CENTER),
                ft.Row(controls=[image_url_field], 
                    alignment=ft.MainAxisAlignment.CENTER),
                ft.Row(controls=[status_dropdown], 
                    alignment=ft.MainAxisAlignment.CENTER),

                ft.Divider(),

                # Buttons - only once!
                ft.Row(
                    controls=[
                        back_to_list_button,
                        post_button,
                    ],
                    alignment=ft.MainAxisAlignment.CENTER,
                    spacing=12
                ),
            ],
            width=860,
            horizontal_alignment=ft.CrossAxisAlignment.CENTER,
            spacing=10,
            scroll=ft.ScrollMode.AUTO  # in case content overflows
        )



    def render_view():
        page.clean()
        page.appbar = ft.AppBar(
            title=ft.Text("Lost Items"),
            bgcolor=ft.Colors.RED_200,
            leading=ft.IconButton(ft.Icons.ARROW_BACK, on_click=lambda _: on_back()) if on_back else None
        )
        if page.lost_view == "list":
            page.add(
                ft.Column(
                    controls=[layout_list()],
                    horizontal_alignment=ft.CrossAxisAlignment.CENTER,
                    width=1900
                )
            )
        else:
            page.add(
                ft.Column(
                    controls=[layout_create()],
                    horizontal_alignment=ft.CrossAxisAlignment.CENTER,
                    width=1900
                )
            )
        page.update()


    
    render_view()
   
    update_post_button()



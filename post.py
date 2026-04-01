# lost_post.py
import flet as ft
from datetime import datetime

def main(page: ft.Page, on_back=None, posts_store=None) -> None:
    # on_back: callback to return to the main app (e.g., welcome screen)
    if posts_store is None:
        posts_store = []

    # Shared UI state
    page.title = "Lost Item Postings"
    page.window_width = 900
    page.window_height = 700
    page.padding = 20
    page.theme_mode = ft.ThemeMode.LIGHT

    # Views: we'll implement a simple simple router with a current_view variable
    # We'll attach to the page to persist between navigations in this session.
    if not hasattr(page, "lost_view"):
        page.lost_view = "list"  # or "create"

    # Define the controls used in both views
    title_field = ft.TextField(label="Item Title", width=520)
    description_field = ft.TextField(label="Description", multiline=True, width=520, height=120)
    category_field = ft.TextField(label="Category (e.g., Wallet, Keys)", width=520)
    location_field = ft.TextField(label="Last Seen Location", width=520)
    date_field = ft.TextField(label="Date Lost (YYYY-MM-DD)", width=520)
    contact_field = ft.TextField(label="Contact Info", width=520)
    image_url_field = ft.TextField(label="Image URL (optional)", width=520)
    post_button = ft.ElevatedButton("Post Lost Item", width=520, disabled=True)
    back_to_list_button = ft.ElevatedButton("Back to List", width=520)

    # Create Post page header and list page header
    list_header = ft.Text("Lost Items List", size=22, weight=ft.FontWeight.BOLD)
    create_header = ft.Text("Create Lost Item", size=22, weight=ft.FontWeight.BOLD)

    # Helpers
    def update_post_button(_=None):
        post_button.disabled = not (title_field.value and description_field.value and location_field.value)
        page.update()

    title_field.on_change = update_post_button
    description_field.on_change = update_post_button
    location_field.on_change = update_post_button

    # Rendering of list
    posts_list = ft.Column(spacing=8, width=700)

    def render_posts():
        posts_list.controls.clear()
        if not posts_store:
            posts_list.controls.append(ft.Text("No posts yet. Be the first to post."))
        else:
            for p in posts_store:
                card = ft.Card(
                    ft.Column(
                        controls=[
                            ft.Text(p["title"], size=16, weight=ft.FontWeight.BOLD),
                            ft.TextField(p["description"], multiline=True),
                            ft.Row(controls=[ft.Text(f"Category: {p['category']}  |  Location: {p['location']}  |  Date: {p['date']}")]),
                            ft.Row(controls=[ft.Text(f"Contact: {p['contact']}")])
                        ],
                        spacing=6
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
            "image": image_url_field.value or ""
        }
        posts_store.append(post)
        render_posts()
        # Clear form
        title_field.value = ""
        description_field.value = ""
        category_field.value = ""
        location_field.value = ""
        date_field.value = ""
        contact_field.value = ""
        image_url_field.value = ""
        update_post_button()

    back_to_list_button.on_click = lambda _e: (setattr(page, "lost_view", "list"), render_view())

    post_button.on_click = post_item
    # Button to switch back to list from create page
    # List view layout
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


    # Create view layout
    def layout_create():
        return ft.Column(
            controls=[
                create_header,
                ft.Row(controls=[title_field], alignment=ft.MainAxisAlignment.CENTER),
                ft.Row(controls=[description_field], alignment=ft.MainAxisAlignment.CENTER),
                ft.Row(controls=[category_field], alignment=ft.MainAxisAlignment.CENTER),
                ft.Row(controls=[location_field], alignment=ft.MainAxisAlignment.CENTER),
                ft.Row(controls=[date_field], alignment=ft.MainAxisAlignment.CENTER),
                ft.Row(controls=[contact_field], alignment=ft.MainAxisAlignment.CENTER),
                ft.Row(controls=[image_url_field], alignment=ft.MainAxisAlignment.CENTER),
                ft.Row(controls=[post_button, back_to_list_button], alignment=ft.MainAxisAlignment.CENTER, spacing=12),
            ],
            width=1900,
            horizontal_alignment=ft.CrossAxisAlignment.CENTER
        )


    def render_view():
        page.clean()
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


    # Initial render
    render_view()
    # Ensure the Post button is properly enabled/disabled on create view
    update_post_button()



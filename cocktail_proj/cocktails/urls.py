from django.urls import path
from . import views

urlpatterns = [
    path("", views.index, name="index"),
    path("search/", views.search, name="search"),
    path("search/ingredient/", views.search_by_ingredient, name="search_by_ingredient"),
    path("category/", views.by_category, name="by_category"),
    path("cocktail/<str:drink_id>/", views.detail, name="detail"),
    path("random/", views.random_cocktail, name="random"),
]

import requests
from urllib.parse import quote
from django.shortcuts import render

BASE_URL = "https://www.thecocktaildb.com/api/json/v1/1"


def _parse_ingredients(drink):
    """Extract non-null ingredient/measure pairs from a drink dict."""
    ingredients = []
    for i in range(1, 16):
        ingredient = drink.get(f"strIngredient{i}")
        measure = drink.get(f"strMeasure{i}")
        if ingredient:
            ingredients.append({
                "name": ingredient.strip(),
                "measure": measure.strip() if measure else "",
            })
    return ingredients


def index(request):
    """Home page: featured categories."""
    try:
        resp = requests.get(f"{BASE_URL}/list.php?c=list", timeout=5)
        categories = [item["strCategory"] for item in resp.json().get("drinks", [])]
    except Exception:
        categories = []

    return render(request, "cocktails/index.html", {"categories": categories})


def search(request):
    """Search cocktails by name."""
    query = request.GET.get("q", "").strip()
    cocktails = []
    error = None

    if query:
        try:
            resp = requests.get(f"{BASE_URL}/search.php?s={quote(query)}", timeout=5)
            data = resp.json().get("drinks")
            cocktails = data if isinstance(data, list) else []
        except Exception:
            error = "API 요청에 실패했습니다. 잠시 후 다시 시도해주세요."

    return render(request, "cocktails/search.html", {
        "query": query,
        "cocktails": cocktails,
        "error": error,
    })


def search_by_ingredient(request):
    """Search cocktails by ingredient."""
    ingredient = request.GET.get("i", "").strip()
    cocktails = []
    error = None

    if ingredient:
        try:
            resp = requests.get(f"{BASE_URL}/filter.php?i={quote(ingredient)}", timeout=5)
            data = resp.json().get("drinks")
            cocktails = data if isinstance(data, list) else []
        except Exception:
            error = "API 요청에 실패했습니다. 잠시 후 다시 시도해주세요."

    return render(request, "cocktails/search.html", {
        "query": ingredient,
        "cocktails": cocktails,
        "error": error,
        "search_type": "ingredient",
    })


def by_category(request):
    """List cocktails in a category."""
    category = request.GET.get("c", "").strip()
    cocktails = []
    error = None
    try:
        resp = requests.get(f"{BASE_URL}/filter.php?c={quote(category)}", timeout=5)
        data = resp.json().get("drinks")
        cocktails = data if isinstance(data, list) else []
    except Exception:
        error = "API 요청에 실패했습니다."

    return render(request, "cocktails/category.html", {
        "category": category,
        "cocktails": cocktails,
        "error": error,
    })


def detail(request, drink_id):
    """Cocktail detail page."""
    drink = None
    error = None
    try:
        resp = requests.get(f"{BASE_URL}/lookup.php?i={drink_id}", timeout=5)
        drinks = resp.json().get("drinks")
        if drinks:
            drink = drinks[0]
            drink["ingredients"] = _parse_ingredients(drink)
    except Exception:
        error = "칵테일 정보를 불러오지 못했습니다."

    return render(request, "cocktails/detail.html", {
        "drink": drink,
        "error": error,
    })


def random_cocktail(request):
    """Show a random cocktail."""
    drink = None
    error = None
    try:
        resp = requests.get(f"{BASE_URL}/random.php", timeout=5)
        drinks = resp.json().get("drinks")
        if drinks:
            drink = drinks[0]
            drink["ingredients"] = _parse_ingredients(drink)
    except Exception:
        error = "랜덤 칵테일을 불러오지 못했습니다."

    return render(request, "cocktails/detail.html", {
        "drink": drink,
        "error": error,
        "is_random": True,
    })

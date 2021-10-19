function manageProduct()
{
	$("#main_section").load("layout/m_product.html");
	$("#nav_title").html("Manage Product");

}
function fetchProduct()
{
	$("#main_section").load("layout/f_product.html");
	$("#nav_title").html("Fetch Product");

}
function newProduct()
{
	$("#main_section").load("layout/n_product.html");
	$("#nav_title").html("New Product");
}
function addToCart()
{
	$("#main_section").load('layout/add_cart.html');
	$("#nav_title").html("Add Cart");
}
function buyProduct()
{
	$("#main_section").load('layout/buy_product.html');
	$("#nav_title").html("Buy Product");
}
function main()
{
	$("#main_section").load("layout/home.html");
	$("#nav_title").html("Home");
}



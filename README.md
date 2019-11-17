# Gyro MVCBundle

## Goals

Decouple and simplify Symfony controllers by adding various abstractions that
avoid having to use Symfony services or classes inside the controllers.

This allows to write controllers that only have dependencies on the
domain/model and let them act as true "application services" that can even be
testable.

We want to achieve slim controllers that are registered as a service.  The
number of services required in any controller should be very small (2-4).  We
believe Context to controllers should be explicitly passed to avoid hiding it
in services.

Ultimately this should make Controllers testable with lightweight unit- and
integration tests.  Elaborate seperation of Symfony from your business logic
should become unnecessary by building controllers that don't depend on Symfony
from the beginning (except maybe Request/Response classes).

For this reason the following features are provided by this bundle:

- Returning View data from controllers
- Returning RedirectRoute
- Helper for Controllers as Service
- Convert Exceptions from Domain/Library Types to Framework Types

## Installation

Add bundle to your application kernel:

```php
$bundles = array(
    // ...
    new Gyro\Bundle\MVCBundle\GyroMVCBundle(),
);
```

Disable view listener in SensioFrameworkExtraBundle if you are using that (not a requirement anymore):

```yml
# app/config/config.yml
sensio_framework_extra:
    view:
        annotations: false
```

## Returning View data from controllers

### Returning Arrays

This bundle replaces the ``@Extra\Template()`` annotation support
from the Sensio FrameworkExtraBundle, without requiring to add the annotation
to the controller actions.

You can just return arrays from controllers and the template names will
be inferred from Controller+Action-Method names.

```php
<?php
# src/Acme/DemoBundle/Controller/DefaultController.php
namespace Acme\DemoBundle\Controller;

class DefaultController
{
    public function helloAction($name = 'Fabien')
    {
        return array('name' => $name);
    }
}
```

### Returning TemplateView

Two use-cases sometimes occur where returning an array from the controller is not flexible enough:

1. Rendering a template with a different action name.
2. Adding headers to the Response object

For this case you can change the previous example to return a ``TemplateView`` instance:

```php
<?php
# src/Acme/DemoBundle/Controller/DefaultController.php
namespace Acme\DemoBundle\Controller;

use Gyro\MVC\TemplateView;

class DefaultController
{
    public function helloAction($name = 'Fabien')
    {
        return new TemplateView(
            array('name' => $name),
            'hallo', // AcmeDemoBundle:Default:hallo.html.twig instead of hello.html.twig
            201,
            array('X-Foo' => 'Bar')
        );
    }
}
```

**Note:** Contrary to the ``render()`` method on the default Symfony base controller
here the view parameters and the template name are exchanged. This is because
everything except the view parameters are optional.

### Returning ViewModels

Usually controllers quickly gather view related logic that is not properly
extracted into a Twig extension, because of the insignficance of these data
transforming methods. This is why on top of the returning array support you can
also use view models and return them from your actions.

Each view model is a class that maps to exactly one template and can contain
properties + methods that are available under the ``view`` template name in
Twig using the same resolving mechanism as if you are returing arrays.

A view model can be any class as long as it does not extend the Symfony
Response class.

```php
<?php
# src/Acme/DemoBundle/View/Default/HelloView.php
namespace Acme\DemoBundle\View\Default;

class HelloView
{
    public $name;

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function getReversedName()
    {
        return strrev($this->name);
    }
}
```

In your controller you just return the view model:

```php
<?php
# src/Acme/DemoBundle/Controller/HelloController.php

namespace Acme\DemoBundle\Controller;

class HelloController
{
    public function helloAction($name)
    {
        return new HelloView($name);
    }
}
```

It gets rendered as ``AcmeBundle:Hello:hello.html.twig``,
where the view model is available as the ``view`` twig variable:

```
Hello {{ view.name }} or {{ view.reversedName }}!
```

You can optionally extend from ``Gyro\MVC\ViewStruct``.
Every ``ViewStruct`` implementation has a constructor accepting and setting
key-value pairs of properties that exist on the view model class.

## Redirect Route

Redirecting in Symfony is much more likely to happen internally to a given
route. The ``Gyro\MVC\RedirectRoute`` can be returned from
your controller and a listener will turn it into a proper Symfony ``RedirectResponse``:

```php
<?php
# src/Acme/DemoBundle/Controller/DefaultController.php
namespace Acme\DemoBundle\Controller;

use Gyro\MVC\RedirectRoute;

class DefaultController
{
    public function redirectAction()
    {
        return new RedirectRoute('hello', array(
            'name' => 'Fabien'
        ));
    }
}
```

If you want to set headers or different status code you can pass a `Response`
as third argument, which will be used instead of creating a new one.

## Add Cookies, Flash Messages, Cache Headers

when returning a View model, array or redirect route from a controller, without
direct access to the response there is no easy way to add response headers.
This is where PHP generators come in and you can `yield` additional response
metadata:

```php
<?php
# src/Acme/DemoBundle/Controller/DefaultController.php
namespace Acme\DemoBundle\Controller;

use Gyro\MVC\Headers;
use Gyro\MVC\Flash;
use Symfony\Component\HttpFoundation\Cookie;

class DefaultController
{
    public function helloAction($name)
    {
        yield new Cookie('name', $name);
        yield new Headers(['X-Hello' => $name]);
        yield new Flash('warning', 'Hello ' . $name);

        return ['name' => $name];
    }
}
```

## Inject TokenContext into actions

In Symfony access to security related information is available through the
`security.context` service.  This is bad from a design perspective, because it
introduces a stateful service whenever access to security related information
is needed.

To avoid access to the security state from a service, it needs to be passed as
arguments, starting with the controller action.

That is what the `TokenContext` class is for. Just add a typehint for it to
any action and MVCBundle will pass this object into your action. From
it you have access to various security related methods:

```php
<?php
# src/Acme/DemoBundle/Controller/DefaultController.php
namespace Acme\DemoBundle\Controller;

use Gyro\MVC\TokenContext;

class DefaultController
{
    public function redirectAction(TokenContext $context)
    {
        if ($context->hasToken()) {
            $user = $context->getCurrentUser();
        } else if ($context->hasAnonymousToken()) {
            // do anon stuff
        }

        if ($context->isGranted('ROLE_ADMIN')) {
            // do admin stuff
            echo $context->getCurrentUserId();
            echo $context->getCurrentUsername();
        }
    }
}
```

For Symfony a concrete implementation `SymfonyTokenContext` is used for the
interface that uses `security.context` internally.

In unit tests where you want to test the controller you can use the `MockTokenContext`
instead. It doesnt work with complex `isGranted()` checks or the token, but if you only
use the user object it allows very simple test setup.

## Working with FormRequest

Handling forms in Symfony typically leads to complicated, untestable controller actions
that are very tightly coupled to various Symfony services. To avoid having to deal with
`form.factory` inside a controller we introduced a specialized request object
that hides all this:

```php
<?php
# src/Acme/DemoBundle/Controller/DefaultController.php
namespace Acme\DemoBundle\Controller;

use Gyro\MVC\FormRequest;
use Gyro\MVC\RedirectRoute;

class ProductController
{
    private $repository;

    public function __construct(ProductRepository $repository)
    {
        $this->repository;
    }

    public function editAction(FormRequest $formRequest, $id)
    {
        $product = $this->repository->find($id);

        if (!$formRequest->handle(new ProductEditType(), $product)) {
            return array('form' => $formRequest->createFormView(), 'entity' => $product);
        }

        $product = $formRequest->getValidData();

        $this->repository->save($product);

        return new RedirectRoute('Product.show', array('id' => $id));
    }
}
```

In tests you can use `new Gyro\MVC\Form\InvalidFormRequest()` and `new
Gyro\MVC\Form\ValidFormRequest($validData)` to work with forms in tests
for controllers.

## ParamConverter for Session

You can pass the session as an argument to a controller:

```
public function indexAction(Session $session)
{
}
```

## Convert Exceptions

Usually the libraries you are using or your own code throw exceptions that can be turned
into HTTP errors other than the 500 server error. To prevent having to do this in the controller
over and over again you can configure to convert those exceptions in a listener:

    gyro_mvc:
        convert_exceptions:
            Doctrine\ORM\EntityNotFoundException: Symfony\Component\HttpKernel\Exception\NotFoundHttpException
            Doctrine\ORM\ORMException: 500

Notable facts about the conversion:

- Both Target Exception classes or just a HTTP StatusCode can be specified
- Subclasses are checked for as well.
- If you don't define conversions the listener is not registered.
- If an exception is converted the original exception will specifically logged
  before conversion. That means when an exception occurs it will be logged
  twice.

## Turbolinks Support

To improve performance with traditional HTML response webapplications Basecamp
introduced [Turbolinks](https://github.com/turbolinks/turbolinks), a library
that uses Ajax to follow same domain links and then replaces only head title
and body to keep javascript and CSS in place.

The GyroMVCBundle provides out of the box support for the
turbolinks JS library in the browser by setting the `Turbolinks-Location`
header after redirects.

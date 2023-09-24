# Gyro MVCBundle

A small framework on top of Symfony introducing a bunch of conventions,
targeting users that want to upgrade from LTS to LTS.

MVCBUndle decouples and simplifies Symfony controllers by adding various
abstractions that avoid having to use Symfony services or classes inside the
controllers.

This bundle succeeds the "QafooLabsNoFrameworkBundle" package.

## Goals

This allows to write controllers that only have dependencies on the
domain/model and let them act as true "application services" that are easily
testable.

Gyros goal is achieving slim controllers that are registered as a service
explicitly (via YML or XML). The number of services required in any controller
should be very small (2-4). We believe Context to controllers should be
explicitly passed to avoid hiding it in services.

Ultimately this should make Controllers testable with lightweight unit- and
integration tests. Elaborate seperation of Symfony from your business logic
should become unnecessary by building controllers that don't depend on Symfony
from the beginning (except maybe Request/Response classes).

## Installation

From Packagist via Composer:

```shell
composer require gyro/mvc-bundle
```

Add bundle to your application kernel:

```php
$bundles = [
    // ...
    new Gyro\Bundle\MVCBundle\GyroMVCBundle(),
];
```

## Returning View data from controllers

### Returning Arrays

This bundle replaces the ``@Extra\Template()`` annotation support
from the Sensio FrameworkExtraBundle, without requiring to add the annotation
to the controller actions.

You can just return arrays from controllers and the template names will
be inferred from Controller+Action-Method names.

If you return from the `App\Controller` default namespace, then the template is
fetched from ':Ctrl:action.html.twig`.

```php
<?php
# src/App/Controller/DefaultController.php
namespace App\Controller;

class DefaultController
{
    public function helloAction($name = 'Fabien')
    {
        return ['name' => $name]; // :Default:hello.html.twig
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
# src/App/Controller/DefaultController.php
namespace App\Controller;

use Gyro\MVC\TemplateView;

class DefaultController
{
    public function helloAction($name = 'Fabien')
    {
        return new TemplateView(
            ['name' => $name],
            'hallo', // :Default:hallo.html.twig instead of hello.html.twig
            201,
            ['X-Foo' => 'Bar']
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
# src/App/View/Default/HelloView.php
namespace App\View\Default;

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
# src/App/Controller/HelloController.php

namespace App\Controller;

class HelloController
{
    public function helloAction($name)
    {
        return new HelloView($name);
    }
}
```

It gets rendered as ``:Hello:hello.html.twig``,
where the view model is available as the ``view`` twig variable:

```twig
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
# src/App/Controller/DefaultController.php
namespace App\Controller;

use Gyro\MVC\RedirectRoute;

class DefaultController
{
    public function redirectAction()
    {
        return new RedirectRoute('hello', ['name' => 'Fabien']);
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
# src/App/Controller/DefaultController.php
namespace App\Controller;

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

## Execute code after the response was sent

For a simple way to delay work from the controller to Symfony's `kernel.terminate` event 
the Gyro's yield applier abstraction handles a `AfterResponseTask` that accepts a closure
to be executed after `Response::send` is called via event subscriber.

```php
public function registerAction($request): RedirectRoute
{
    $user = $this->createUser($request);
    $this->entityManager->persist($user);

    yield new AfterResponseTask(fn () => $this->sendEmail($user));

    return new RedirectRoute('home');
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
# src/App/Controller/DefaultController.php
namespace App\Controller;

use Gyro\MVC\TokenContext;

class DefaultController
{
    public function redirectAction(TokenContext $context)
    {
        if ($context->hasToken()) {
            $user = $context->getCurrentUser(MyUser::class);
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

The methods `getCurrentUser` and `getToken` expect a concrecte class name string
as first argument, in this example it is `MyUser::class`. This is used with Psalm
template annotations to improve static analysis.

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
# src/App/Controller/ProductController.php

namespace App\Controller;

use Gyro\MVC\FormRequest;
use Gyro\MVC\RedirectRoute;

class ProductController
{
    private $repository;

    public function __construct(ProductRepository $repository)
    {
        $this->repository = $repository;
    }

    public function editAction(FormRequest $formRequest, $id)
    {
        $product = $this->repository->find($id);

        if (!$formRequest->handle(new ProductEditType(), $product)) {
            return ['form' => $formRequest->createFormView(), 'entity' => $product];
        }

        $product = $formRequest->getValidData();

        $this->repository->save($product);

        return new RedirectRoute('Product.show', ['id' => $id]);
    }
}
```

In tests you can use `new Gyro\MVC\Form\InvalidFormRequest()` and `new
Gyro\MVC\Form\ValidFormRequest($validData)` to work with forms in tests
for controllers.

## ParamConverter for Session

You can pass the session as an argument to a controller:

```php
public function indexAction(Session $session)
{
}
```

## Convert Exceptions

Usually the libraries you are using or your own code throw exceptions that can be turned
into HTTP errors other than the 500 server error. To prevent having to do this in the controller
over and over again you can configure to convert those exceptions in a listener:

```yaml
# config/packages/gyro_mvc.yml
gyro_mvc:
    convert_exceptions:
        Doctrine\ORM\EntityNotFoundException: Symfony\Component\HttpKernel\Exception\NotFoundHttpException
        Doctrine\ORM\ORMException: 500
```

Notable facts about the conversion:

- Both Target Exception classes or just a HTTP StatusCode can be specified
- Subclasses are checked for as well.
- If you don't define conversions the listener is not registered.
- If an exception is converted the original exception will specifically logged
  before conversion. That means when an exception occurs it will be logged
  twice.

The following excpetions are registered by default:

| Exception Class                                               | Converted To                                                  |
| ------------------------------------------------------------- | ------------------------------------------------------------- |
| Doctrine\ORM\EntityNotFoundException                          | Symfony\Component\HttpKernel\Exception\NotFoundHttpException  |
| Elasticsearch\Common\Exceptions\Missing404Exception           | Symfony\Component\HttpKernel\Exception\NotFoundHttpException  |

## EventDispatcher Adapter

The API of Symfony EventDispatcher changed in special way between version 3 and
4 and will again in 5. You don't pass the event name anymore, as required first
argument but now you may pass it as optional second argument. This was done to
align Symfony with [PSR-14
(Event-Dispatcher)](https://www.php-fig.org/psr/psr-14/).

The migration path for this code is a bit annoying and when using Psalm will
lead to violations that need to be suppressed.

Gyro ships an adapter for the EventDispatcher that avoids this problem. Its API
is PSR-14 API compatible, but does not implement the interface. It then
delegates to Symfony event dispatchers correctly.

Inject the service `gyro_mvc.event_dispatcher` instead of the
`event_dispatcher` service.

```php
use Gyro\MVC\EventDispatcher\EventDispatcher;

class MyEvent
{
}

class MyService
{
    private EventDispatcher $eventDispatcher;

    public function __construct(EventDispatcher $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function performMyOperation()
    {
        // ....
        $this->eventDispatcher->dispatch(new MyEvent());
    }
}
```

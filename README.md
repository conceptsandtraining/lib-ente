# ente.php

An entity component framework for PHP.

**Contact:** [Richard Klees](https://github.com/klees)

## Model

[Entity component model or system is an architectural pattern](https://en.wikipedia.org/wiki/Entity%E2%80%93component%E2%80%93system)
developed for and mostly used in games. Still, the problems solved with that
pattern are discovered in other situations, i.e. the development of interconnected
functionality in [ILIAS](https://github.com/ILIAS-eLearning/ILIAS).

The general problem that is solved could be described as such: One has a system
with some (or often a lot of) entities in it. An entity can be understood as an
object that has an identity and exhibits some continuity over time and space.
Within the system, there are a lot of subsystems that act upon these entities.

> **Example:**
> In a real time strategy game entities would be units of the armies in the game
> as well as objects in the landscape and maybe other things. Systems acting
> upon these objects would be e.g. a physics system, a path finding system, a
> sound system, ...

> **Example:**
> In ILIAS an entity could basically be identified with all descendants of `ilObject`
> although there are possibly more things that could be called entities, e.g.
> questions in the test. Systems acting upon these entities would then e.g. be the
> RBAC system, the Learning Progress System, the Tree/Repository, ...

Implementing that kind of system with inheritance could lead to some problems that
could also be observed in ILIAS:

* The classes that are used to build entities aqcuire a lot of code to be able to
  serve all systems that are interested in them.
* It is difficult to share functionality between the different classes of entities.
* It is cumbersome to define classes of entities where the objects might or might
  not take part in that system, depending on whatever.
* For languages that are not memory managed (e.g. C) that strategy for implementation
  might also be bad for caching in CPU caches.

Entity component model solves these problems by employing the principle to "favour
composition over inheritance". The entities on their own are understood to just
supply the required identity and continuity, i.e. each entity is basically an id.
Components that serve the needs of different subsystems can then be attached to
the entity to make them take part in that system.

> **Example:**
> A entity in the RTS game could be extended with a "physics" component that
> knows a location and other physical metrics of the entity to make it appear
> on the playground.

> **Example:**
> The implementation of the learning progress in ILIAS actually follows that
> model. Instead of providing some interface that all `ilObjects` with learning
> progress need to implement, one needs to build a separate object that takes
> care of the learning progress of the original `ilObject`.

This pattern for solving the described problem has some virtues over the naive
inheritance based approach:

* Instead of one huge class that contains code for all kind of subsystems the
  entity is split up into distinct classes that each serves only some subset
  of all systems.
* There is an obvious way to share functionality between entities: just use
  the same components or implementation for the component.
* If an entity should not be processed by a subsystem just don't add the
  according component to the entity. This could easily be changed at runtime
  and requires no code change.
* C-programmes and the like could take care about memory locality more easily.
  A similar benefit regarding the database could apply in the PHP scenario.

There are definetly also disadvantages in using the entity component model. For
the sake of the argument they are not written down here. Finding them is left as
an exercise to the reader.

## Implementation

This implementation is meant to work in the context of ILIAS, where Plugins should
be able to define components for `ilObjects`. This library therefore knows four
kinds of objects, where the according interfaces could be found in the base directory
of the lib:

* `Entity` provides the basic means to identify an object. That is, an entityi
  needs to be able to provide some comparable id, where objects with the same id
  are indeed the very same object. This is the thingy known from the pattern.
* A `Component` is the thingy from the pattern that provides information and
  implementations for a certain subsystem. Since the library doesn't know about
  these subsystems, the basic interface just provides a method to get to know
  the entity the component belongs to. This will be one main extension point
  for this library.
* This library should be able to work in the context of `ilObjects`, plugins and
  the ILIAS tree. The plugins this framework will be used for are mainly there
  to enhance the ILIAS Course with different functionality. Plugins then need
  to be able to extend the course in different directions, thus a notion of
  sources for components of an entity is required. A `Provider` thus can provide
  different types of components for a fixed entity. 
*  

## Example

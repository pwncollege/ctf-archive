// prepare the inputs
const expression_input = document.getElementById("expression");
const draw_button = document.getElementById("draw");

// prepare the canvas
const canvas = document.getElementById("canvas");
const app = new PIXI.Application();
await app.init({
  view: canvas,
  antialias: false,
  backgroundColor: 0xffffff,
  // backgroundAlpha: 0,
  // transparent: true,
  resolution: devicePixelRatio,
});

const canvas_transform = {
  // move (0,0) to center of canvas
  ox: app.renderer.width / 2,
  oy: app.renderer.height / 2,
  // flip y axis to be positive going up
  sx: +(app.renderer.width / 2 / 100),
  sy: -(app.renderer.height / 2 / 100),
};

// get a range of values from start to end each step, inclusive
const range = function* (start = 0, end = 1, step = 1) {
  let t = Math.floor((end - start) / step);
  if (t > 0) {
    let c = start;
    yield [c, t];
    for (let i = 1; i < t; i += 1) {
      c += step;
      yield [c, t];
    }
  }
};

const to_xy = (x, map) => {
  // console.log("to_xy", x, map);
  let results = { x };
  let remain;
  let found;
  let i = 0;
  do {
    remain = false;
    found = false;
    for (const [v, f, args] of map._data) {
      if (results.hasOwnProperty(v) && v !== "x") {
        // if already solved but allow overriding x
        // console.log("Optimized", v);
        continue;
      }
      // split args into an array
      let sargs = Array.prototype.filter.call(
        Array.prototype.map.call(String.prototype.split.call(args, ","), (e) => {
          return String.prototype.trim.call(e);
        }),
        (e) => {
          return (typeof e === "string" || e instanceof String) && e.length > 0;
        }
      );
      if (
        // if all argument values have been solved
        Array.prototype.every.call(sargs, (a) => {
          return results.hasOwnProperty(a);
        })
      ) {
        // solve
        const r = f(
          ...Array.prototype.map.call(sargs, (a) => {
            return results[a];
          })
        );
        // store the solution
        results[v] = r;
        // console.log("Found", v, r, results);
        found = true;
      } else {
        // console.log("Remains", v, sargs);
        remain = true;
      }
    }
    // console.log(remain, found);
    i += 1;
  } while (remain && found && i < 10);
  // console.log("results before", results);
  // keep only valid results
  results = { x: results["x"], y: results["y"] };
  for (const [k, v] of Object.entries(results)) {
    if (typeof v !== "number" || Number.isNaN(v)) {
      delete results[k];
    }
  }
  // console.log("results after", results);
  return results;
};

// get x,y values from an expression in the given range
const xy = function* (code, range, transform = { ox: 0, oy: 0, sx: 1, sy: 1 }) {
  for (let [x, t] of range) {
    let y = code.evaluate({ x, to_xy });
    // console.log("Y", y);
    if (typeof y === "object" && !Array.isArray(y) && y !== null) {
      y = y.entries;
    }
    if (Array.isArray(y)) {
      y = y[y.length - 1];
    }
    if (typeof y === "object" && !Array.isArray(y) && y !== null) {
      x = y["x"];
      y = y["y"];
    }
    yield [
      {
        x: transform.ox + x * transform.sx,
        y: transform.oy + y * transform.sy,
      },
      t,
    ];
  }
};

const prepare_math = () => {
  const math = window.math.create(window.math.all);
  const parse = math.parse;
  math.import(
    {
      import: () => {
        throw new Error("Function import is disabled");
      },
      createUnit: () => {
        throw new Error("Function createUnit is disabled");
      },
      reviver: () => {
        throw new Error("Function reviver is disabled");
      },
      evaluate: () => {
        throw new Error("Function evaluate is disabled");
      },
      parse: () => {
        throw new Error("Function parse is disabled");
      },
      simplify: () => {
        throw new Error("Function simplify is disabled");
      },
      derivative: () => {
        throw new Error("Function derivative is disabled");
      },
      resolve: () => {
        throw new Error("Function resolve is disabled");
      },
    },
    { override: true }
  );
  return [math, parse];
};

const draw = (expr, transform = canvas_transform) => {
  const [math, parse] = prepare_math();
  let axes_ranges = { l: -100, r: 100, b: -100, t: 100 };
  let animation_duration = 3.0;
  // get the code from the expression
  const code = parse(expr).compile();
  // get points from the code
  const xys = xy(code, range(axes_ranges.l, axes_ranges.r, 1), transform);
  // draw a curve following the points
  let curve = new PIXI.Graphics();
  app.stage.addChild(curve);
  const nxt = xys.next();
  if (!nxt.done) {
    const [point, total_points] = nxt.value;
    curve.moveTo(point.x, point.y);
    let points_done = 0;
    let elapsed = 0.0;
    const tick = (ticker) => {
      elapsed += ticker.elapsedMS / 1000.0;
      const points_to_do = Math.floor((elapsed / animation_duration) * total_points - points_done);
      if (points_to_do >= 1) {
        for (const i of range(0, points_to_do, 1)) {
          const nxt = xys.next();
          if (!nxt.done && points_to_do > 0) {
            const [point, total_points] = nxt.value;
            curve.lineTo(point.x, point.y).stroke({
              color: 0xff0000,
              width: 1,
              alignment: 0.5,
            });
          } else {
            app.ticker.remove(tick);
            break;
          }
        }
        points_done += points_to_do;
      }
    };
    app.ticker.add(tick);
  }
};

// prepare the input event handlers

const handler = (event) => {
  app.stage.removeChildren();
  const expr = expression_input.value;
  // console.log(math.parse(expr).compile().evaluate({ x: x, to_xy: to_xy }));
  // return;
  draw(expr);
};

expression_input.onkeydown = (event) => {
  if (event.key === "Enter") {
    handler(event);
  }
};

draw_button.addEventListener("click", (event) => {
  handler(event);
});

// handle expression in fragment
const fragment_handler = (event) => {
  const he = window.location.hash.replace(/^#/, "");
  if (he) {
    expression_input.value = he;
    handler(null);
  }
};

window.addEventListener("hashchange", fragment_handler);

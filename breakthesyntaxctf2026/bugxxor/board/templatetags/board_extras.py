from django import template

register = template.Library()


@register.filter
def get_bug_info(obj, attr):
    return getattr(obj, attr)

@register.filter
def leet(value):
    table = str.maketrans("aAeEiIoOsStTlL", "44331100557711")
    return value.translate(table)
